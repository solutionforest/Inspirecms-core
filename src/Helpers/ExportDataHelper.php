<?php

namespace SolutionForest\InspireCms\Helpers;

use SolutionForest\InspireCms\InspireCmsConfig;

class ExportDataHelper
{
    public static function getDiskDriver(): string
    {
        return strval(InspireCmsConfig::get('import_export.exports.disk', 'public'));
    }

    public static function getDirectory(): string
    {
        return strval(InspireCmsConfig::get('import_export.exports.directory', 'exports'));
    }

    public static function getTempDiskDriver(): string
    {
        return strval(InspireCmsConfig::get('import_export.exports.temporary.disk', 'local'));
    }

    public static function getTempDirectory(): string
    {
        return strval(InspireCmsConfig::get('import_export.exports.temporary.directory', 'temp/exports'));
    }

    public static function retrieveClearanceDaysInterval()
    {
        return InspireCmsConfig::get('models.prunable.export.interval', 5);
    }

    public static function getExporters(): array
    {
        $exporters = InspireCmsConfig::get('import_export.exports.exporters', [
            \SolutionForest\InspireCms\Exports\Exporters\ImportUsedExporter::class,
            \SolutionForest\InspireCms\Exports\Exporters\DocumentTypeExporter::class,
            \SolutionForest\InspireCms\Exports\Exporters\FieldGroupExporter::class,
            \SolutionForest\InspireCms\Exports\Exporters\TemplateExporter::class,
        ]);

        return collect($exporters)
            ->mapWithKeys(fn ($exporter) => [$exporter => $exporter::getLabel()])
            ->all();
    }
}
