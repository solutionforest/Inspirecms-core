<?php

namespace SolutionForest\InspireCms\Filament\Contracts;

interface ClusterSectionResource extends HasPermissions
{
    public static function getCluster(): ?string;

    public static function getClusterSection(): string;
}
