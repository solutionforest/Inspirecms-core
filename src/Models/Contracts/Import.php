<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\CarbonInterface;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use SolutionForest\InspireCms\Base\Enums\ImportStatus;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\CanPrunable;
use SolutionForest\InspireCms\Support\Models\Contracts\HasAuthor;
use Throwable;

/**
 * @property string $id
 * @property string $file_disk
 * @property string $file_name
 * @property ?string $payload
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $available_at
 * @property ?CarbonInterface $finished_at
 * @property ?CarbonInterface $failed_at
 * @property-read ?ImportStatus $display_status
 * @property-read ?CarbonInterface $clear_at
 */
interface Import extends CanPrunable, HasAuthor
{
    /**
     * Get the storage and file path for the import job.
     *
     * @return array{0: Filesystem|FilesystemAdapter, 1: string}
     *
     * @throws Exception if the disk is not set for the import job.
     */
    public function getStorageAndFilePath();

    /**
     * Marks the import job as failed with the given message.
     *
     * @param  string|Throwable|array  $msg  The failure message to be recorded.
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
     * Scope a query to only include pending import jobs.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWherePending($query, bool $condition = true);

    /**
     * Scope a query to only include completed jobs.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWhereCompleted($query);

    /**
     * Scope a query to only include failed jobs.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWhereFailed($query);

    /**
     * Scope a query to only include records that can be cleared.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWhereCanClear($query);
}
