<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Storage;
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
        $this->update([
            'failed_at' => now(),
            'payload' => $msg,
        ]);
    }

    public function markAsCompleted($filename, $msg = null)
    {
        $this->update([
            'file_name' => $filename,
            'finished_at' => now(),
            'payload' => $msg,
        ]);
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
    // endregion Helper(s)
}
