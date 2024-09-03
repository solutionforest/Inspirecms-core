<?php
 
namespace SolutionForest\InspireCms\Filament\Clusters;
 
use Filament\Clusters\Cluster;
 
class Contents extends Cluster
{
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