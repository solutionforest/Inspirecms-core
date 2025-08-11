<?php

namespace SolutionForest\InspireCms\Filament\Clusters;

use Filament\Clusters\Cluster;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;

class Content extends Cluster implements ClusterSection
{
    use ClusterSectionTrait;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document';

    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-s-document';

    protected static ?int $navigationSort = -10;

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::inspirecms.content.plural');
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return __('inspirecms::inspirecms.content.plural');
    }
}
