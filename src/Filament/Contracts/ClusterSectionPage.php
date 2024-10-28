<?php

namespace SolutionForest\InspireCms\Filament\Contracts;

interface ClusterSectionPage
{
    public static function getCluster(): ?string;

    public static function getClusterSection(): string;
}
