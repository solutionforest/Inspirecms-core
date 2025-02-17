<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use SolutionForest\InspireCms\Support\Base\Models\Interfaces\CanPrunable;
use SolutionForest\InspireCms\Support\Models\Contracts\HasAuthor;

/**
 * @property string $id
 * @property string $file_disk
 * @property ?string $file_name
 * @property string $exporter
 * @property ?string $payload
 * @property ?\Carbon\Carbon $created_at
 * @property ?\Carbon\Carbon $finished_at
 * @property ?\Carbon\Carbon $failed_at
 */
interface Export extends CanPrunable, HasAuthor
{
    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
     *
     * @throws \Exception if the disk is not set for the import job.
     */
    public function getDisk();

    /**
     * Marks the export process as failed.
     *
     * This method should be called when the export process encounters an error
     * or cannot be completed successfully. Implementations of this method
     * should handle any necessary cleanup or state changes to reflect the
     * failure of the export process.
     *
     * @param  string|\Throwable|array  $msg  The failure message to be recorded.
     * @return void
     */
    public function markAsFailed($msg);

    /**
     * Marks the export process as completed.
     *
     * @param  string  $filename  The name of the file that was exported.
     * @param  string|array|null  $msg  Optional message to be associated with the completion status.
     * @return void
     */
    public function markAsCompleted($filename, $msg = null);

    /**
     * Scope a query to only include pending items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWherePending($query, bool $condition = true);

    /**
     * Scope a query to only include completed items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereCompleted($query);

    /**
     * Scope a query to only include records where the export has failed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereFailed($query);
}
