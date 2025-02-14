<?php

namespace SolutionForest\InspireCms\Exporters;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Export;

abstract class BaseExporter
{
    /**
     * @param Export & Model $export
     */
    public function __construct(
        protected $export,
    ) { }

    /**
     * @return string The result filename.
     */
    abstract public function export();

    /**
     * @param  string  $folderName
     */
    protected function generateTempFolder($folderName)
    {
        $disk = $this->export->getDisk();

        // Create directory with permissions
        if (! $disk->exists($folderName)) {
            $disk->makeDirectory($folderName, 0777, true);
        }

        return [$disk, $disk->path($folderName)];
    }
}
