<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Base\Enums\ImportJobStatus;
use SolutionForest\InspireCms\Models\Contracts\ImportJob as ImportJobContract;
use SolutionForest\InspireCms\Observers\ImportJobObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Concerns\HasAuthor;

class ImportJob extends BaseModel implements ImportJobContract
{
    use HasAuthor;
    use HasUuids;

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
        return [$this->getStorageDisk(), $this->file];
    }

    public function markAsFailed($msg)
    {
        if ($msg instanceof \Throwable) {
            $msg = [
                'exMessage' => $msg->getMessage(),
                'exTrace' => $msg->getTrace(),
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
                    return ImportJobStatus::Finished;
                } elseif (! is_null($failedTime)) {
                    return ImportJobStatus::Failed;
                }

                return ImportJobStatus::Pending;
            },
            set: function ($value) {}
        );
    }

    public function clearAt(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->display_status == ImportJobStatus::Pending) {
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
        return config('inspirecms.imports.disk');
    }

    //region Scope(s)
    public function scopeWherePending($query, bool $condition = true)
    {
        if ($condition) {
            return $query->whereNull('finished_at')->where('available_at', '<=', now());
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

        static::observe(ImportJobObserver::class);
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
        $disk = $this->disk;

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
            $this->getStorageDisk()->delete($this->file);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    protected static function retrieveClearanceDaysInterval()
    {
        return config('inspirecms.scheduled_tasks.cleanup_import_job.old_import_job_days', 30);
    }
    //endregion Helper(s)
}
