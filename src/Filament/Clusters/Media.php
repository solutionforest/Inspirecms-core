<?php

namespace SolutionForest\InspireCms\Filament\Clusters;

use Filament\Clusters\Cluster;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;
use SolutionForest\InspireCms\Support\MediaLibrary\Contracts\MediaLibraryPage;

class Media extends Cluster implements ClusterSection, MediaLibraryPage
{
    use ClusterSectionTrait;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-s-photo';

    protected static ?int $navigationSort = -9;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.pages.media-library';

    public function mount(): void
    {
        // avoid redirecting to the first item in the list
    }

    public function getSubNavigation(): array
    {
        return [];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationItemActiveRoutePattern(): string
    {
        return static::getRouteName();
    }

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::inspirecms.media');
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return __('inspirecms::inspirecms.media');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }
}
