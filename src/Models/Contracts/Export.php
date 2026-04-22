<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use SolutionForest\InspireCms\Base\Enums\ExportStatus;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\CanPrunable;
use SolutionForest\InspireCms\Support\Models\Contracts\HasAuthor;

/**
 * @property string $id
 * @property string $file_disk
 * @property ?string $file_name
 * @property string $exporter
 * @property ?array $payload
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $finished_at
 * @property ?CarbonInterface $failed_at
 * @property-read ?ExportStatus $display_status
 * @property-read ?ExportStatus $display_exporter
 * @property-read ?CarbonInterface $clear_at
 */
interface Export extends CanPrunable, HasAuthor
{
    /**
     * @return Filesystem|FilesystemAdapter
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
     * Marks the current process as paused.
     *
     * @param  array  $messages  Data or context needed to mark the process as paused.
     * @return void
     */
    public function markAsPaused($messages);

    /**
     * Retrieve the processing messages.
     *
     * @return array The processing messages.
     */
    public function getProcessingMessages();

    /**
     * Get the arguments required for the exporter.
     *
     * @return array
     */
    public function getArgsForExporter();

    public function isCompleted(): bool;

    /**
     * Scope a query to only include pending items.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWherePending($query, bool $condition = true);

    /**
     * Scope a query to only include completed items.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWhereCompleted($query);

    /**
     * Scope a query to only include records where the export has failed.
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
