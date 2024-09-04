<?php

namespace SolutionForest\InspireCms\Filament\Clusters;

use Filament\Clusters\Cluster;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;

class Contents extends Cluster implements ClusterSection
{
    use ClusterSectionTrait;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::inspirecms.contents');
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return __('inspirecms::inspirecms.contents');
    }
}
