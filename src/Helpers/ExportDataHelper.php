<?php

namespace SolutionForest\InspireCms\Helpers;

use SolutionForest\InspireCms\InspireCmsConfig;

class ExportDataHelper
{
    public static function getDiskDriver(): string
    {
        return strval(InspireCmsConfig::get('exports.disk', 'public'));
    }

    public static function retrieveClearanceDaysInterval()
    {
        return InspireCmsConfig::get('models.prunable.export.interval', 5);
    }
}
