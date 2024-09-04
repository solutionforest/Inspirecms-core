<?php

namespace SolutionForest\InspireCms\Filament\Contracts;

use SolutionForest\InspireCms\Filament\Contracts\HasPermissions;

interface ClusterSectionResource extends HasPermissions
{
    public static function getCluster(): ?string;

    public static function getClusterSection(): string;
}
