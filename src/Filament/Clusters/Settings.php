<?php
 
namespace SolutionForest\InspireCms\Filament\Clusters;
 
use Filament\Clusters\Cluster;
 
class Settings extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::inspirecms.settings');
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return __('inspirecms::inspirecms.settings');
    }
}