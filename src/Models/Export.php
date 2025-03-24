<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Base\Enums\ExportStatus;
use SolutionForest\InspireCms\Exports\Exporters\BaseExporter;
use SolutionForest\InspireCms\Helpers\ExportDataHelper;
use SolutionForest\InspireCms\Helpers\ThrowableHelper;
use SolutionForest\InspireCms\Models\Contracts\Export as ExportContract;
use SolutionForest\InspireCms\Observers\ExportObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Concerns\HasAuthor;

class Export extends BaseModel implements ExportContract
{
    use HasAuthor;
    use HasUuids;
    use Prunable;

    const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'finished_at' => 'datetime',
        'failed_at' => 'datetime',
        'payload' => 'json',
    ];

    public function markAsFailed($msg)
    {
        if ($msg instanceof \Throwable) {
            $msg = [
                'exMessage' => $msg->getMessage(),
                'exTrace' => ThrowableHelper::getTraceAsString($msg, 5),
            ];
        }

        $payload = $this->mergeResultToPayload($msg);

        $this->update([
            'failed_at' => now(),
            'payload' => $payload,
        ]);
    }

    public function markAsCompleted($filename, $msg = null)
    {
        $payload = $this->mergeResultToPayload($msg);
        // Remove processing data
        unset($payload['processing']);

        $this->update([
            'file_name' => $filename,
            'finished_at' => now(),
            'payload' => $payload,
        ]);
    }

    public function markAsPaused($messages)
    {
        $this->update([
            'payload' => $this->mergeProcessingToPayload($messages),
        ]);
    }

    public function getProcessingMessages()
    {
        return $this->payload['processing'] ?? [];
    }

    public function getArgsForExporter()
    {
        return $this->payload['args'] ?? [];
    }

    public function delete()
    {
        $this->deleteFile();

        parent::delete();
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
     *
     * @throws \Exception if the disk is not set for the import job.
     */
    public function getDisk()
    {
        $disk = $this->file_disk;

        if (empty($disk)) {
            throw new \Exception('Disk is not set for the import job.');
        }

        return Storage::disk($disk);
    }

    public function isCompleted(): bool
    {
        return $this->finished_at !== null;
    }

    // region Prunable
    /**
     * Get the prunable model query.
     */
    public function prunable()
    {
        return static::query()->wherePending(false)->where('created_at', '<=', now()->subDays(ExportDataHelper::retrieveClearanceDaysInterval()));
    }

    /**
     * Prepare the model for pruning.
     *
     * @return void
     */
    protected function pruning()
    {
        $this->deleteFile();
    }
    // endregion Prunable

    // region Scope(s)
    public function scopeWherePending($query, bool $condition = true)
    {
        if ($condition) {
            return $query->whereNull('finished_at')->whereNull('failed_at');
        } else {
            return $query->whereNotNull('finished_at')->orWhereNotNull('failed_at');
        }
    }

    public function scopeWhereCompleted($query)
    {
        return $query->whereNotNull('finished_at');
    }

    public function scopeWhereFailed($query)
    {
        return $query->whereNotNull('failed_at');
    }
    // endregion Scope(s)

    public function displayStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                [$failed, $finished, $processingMsg] = [$this->failed_at, $this->finished_at, $this->getProcessingMessages()];
                if ($failed !== null) {
                    return ExportStatus::Failed;
                } elseif ($finished !== null) {
                    return ExportStatus::Finished;
                } elseif (!empty($processingMsg)) {
                    return ExportStatus::InProgress;
                } else {
                    return ExportStatus::Pending;
                }
            },
            set: function ($value) {}
        );
    }

    public function displayExporter(): Attribute
    {
        return Attribute::make(
            get: function () {
                $exporter = $this->exporter;

                if (filled($exporter) && class_exists($exporter) && is_a($exporter, BaseExporter::class, true)) {
                    return $exporter::getLabel();
                }

                return null;
            },
            set: function ($value) {}
        );
    }

    public static function boot()
    {
        parent::boot();

        static::observe(ExportObserver::class);
    }

    // region Helper(s)

    /**
     * Deletes the file associated with the import job.
     *
     * This method is responsible for removing the file from the filesystem
     * that was used during the import process.
     *
     * @return bool
     */
    protected function deleteFile()
    {
        try {
            $this->getDisk()->delete($this->file_name);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    /**
     * @param  mixed  $msg
     * @return array
     */
    private function mergeResultToPayload($msg)
    {
        $payload = $this->payload ?? [];
        if (! is_array($msg)) {
            $msg = ['messages' => $msg];
        }
        $payload['result'] = array_merge($payload['result'] ?? [], $msg ?? []);

        return $payload;
    }

    private function mergeProcessingToPayload($msg)
    {
        $payload = $this->payload ?? [];
        if (! is_array($msg)) {
            $msg = ['messages' => $msg];
        }
        $payload['processing'] = array_merge($payload['processing'] ?? [], $msg);

        return $payload;
    }
    // endregion Helper(s)
}
