<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Base\Enums\ImportStatus;
use SolutionForest\InspireCms\Helpers\ThrowableHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Import as ImportContract;
use SolutionForest\InspireCms\Observers\ImportObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Concerns\HasAuthor;

class Import extends BaseModel implements ImportContract
{
    use HasAuthor;
    use HasUuids;
    use Prunable;

    const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'available_at' => 'datetime',
        'finished_at' => 'datetime',
        'failed_at' => 'datetime',
        'payload' => 'json',
    ];

    public function getStorageAndFilePath()
    {
        return [$this->getStorageDisk(), $this->file_name];
    }

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

    public function markAsCompleted($msg = null)
    {
        $this->update([
            'finished_at' => now(),
            'payload' => $msg,
        ]);
    }

    public function displayStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                [$finishTime, $failedTime, $scheduleTime] = [$this->finished_at, $this->failed_at, $this->available_at];
                if (! is_null($finishTime)) {
                    return ImportStatus::Finished;
                } elseif (! is_null($failedTime)) {
                    return ImportStatus::Failed;
                }

                return ImportStatus::Pending;
            },
            set: function ($value) {}
        );
    }

    public function clearAt(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->display_status == ImportStatus::Pending) {
                    return null;
                }

                return $this->created_at?->addDays(static::retrieveClearanceDaysInterval());
            },
            set: function ($value) {}
        );
    }

    public function delete()
    {
        $this->deleteFile();

        parent::delete();
    }

    public static function getDiskDriver()
    {
        return InspireCmsConfig::get('imports.disk');
    }

    //region Prunable
    /**
     * Get the prunable model query.
     */
    public function prunable()
    {
        return static::whereCanClear();
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
    //endregion Prunable

    //region Scope(s)
    public function scopeWherePending($query, bool $condition = true)
    {
        if ($condition) {
            return $query->where('available_at', '<=', now())->whereNull('finished_at')->whereNull('failed_at');
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

    public function scopeWhereCanClear($query)
    {
        return $query
            ->wherePending(false)
            ->where('created_at', '<', now()->subDays(static::retrieveClearanceDaysInterval()));
    }
    //endregion Scope(s)

    public static function boot()
    {
        parent::boot();

        static::observe(ImportObserver::class);
    }

    //region Helper(s)
    /**
     * Get the storage disk used for the import job.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem The storage disk instance.
     *
     * @throws \Exception if the disk is not set for the import job.
     */
    protected function getStorageDisk()
    {
        $disk = $this->file_disk;

        if (empty($disk)) {
            throw new \Exception('Disk is not set for the import job.');
        }

        return Storage::disk($disk);
    }

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
            $this->getStorageDisk()->delete($this->file_name);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    protected static function retrieveClearanceDaysInterval()
    {
        return InspireCmsConfig::get('models.prunable.import.interval', 5);
    }
    //endregion Helper(s)
}
