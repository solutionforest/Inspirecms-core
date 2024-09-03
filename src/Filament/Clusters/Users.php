<?php
 
namespace SolutionForest\InspireCms\Filament\Clusters;
 
use Filament\Clusters\Cluster;
 
class Users extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::inspirecms.users');
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return __('inspirecms::inspirecms.users');
    }
}