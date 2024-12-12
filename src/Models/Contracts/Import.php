<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use SolutionForest\InspireCms\Base\Enums\ImportStatus;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\CanPrunable;
use SolutionForest\InspireCms\Support\Models\Contracts\HasAuthor;

/**
 * @property int $id
 * @property string $type
 * @property string $file_disk
 * @property string $file_name
 * @property ?string $payload
 * @property ?\Carbon\Carbon $created_at
 * @property ?\Carbon\Carbon $available_at
 * @property ?\Carbon\Carbon $finished_at
 * @property ?\Carbon\Carbon $failed_at
 * @property ?ImportStatus $display_status
 * @property ?\Carbon\Carbon $clear_at
 */
interface Import extends HasAuthor, CanPrunable
{
    /**
     * Get the storage and file path for the import job.
     *
     * @return array{0:\Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter,1:string}
     *
     * @throws \Exception if the disk is not set for the import job.
     */
    public function getStorageAndFilePath();

    /**
     * Marks the import job as failed with the given message.
     *
     * @param  string|\Throwable|array  $msg  The failure message to be recorded.
     * @return void
     */
    public function markAsFailed($msg);

    /**
     * Marks the import job as completed.
     *
     * @param  string|array|null  $msg  Optional message to be associated with the completion status.
     * @return void
     */
    public function markAsCompleted($msg = null);

    /**
     * Get the disk driver for the Import.
     *
     * @return string The name of the disk driver.
     */
    public static function getDiskDriver();

    /**
     * Scope a query to only include pending import jobs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWherePending($query, bool $condition = true);

    /**
     * Scope a query to only include completed jobs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereCompleted($query);

    /**
     * Scope a query to only include failed jobs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereFailed($query);

    /**
     * Scope a query to only include records that can be cleared.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereCanClear($query);
}
