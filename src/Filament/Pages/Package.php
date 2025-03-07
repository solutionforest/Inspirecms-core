<?php

namespace SolutionForest\InspireCms\Filament\Pages;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportResource;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Livewire\ListPackages;

class Package extends Page implements ClusterSectionPage, HasForms, HasActions, HasInfolists
{
    use ClusterSectionPageTrait {
        ClusterSectionPageTrait::canAccess as traitCanAccess;
    }
    use InteractsWithForms;
    use InteractsWithInfolists;
    use InteractsWithActions;

    public static string $view = 'inspirecms::filament.pages.package';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $cluster = Settings::class;

    public static function canAccess(): bool
    {
        return static::getImportResource()::canViewAny() || static::getExportResource()::canViewAny();
    }

    public function getTableComponents(): array
    {
        $components = [];

        if (static::getImportResource()::canViewAny()) {
            $components[] = [
            'component' => ListPackages::class,
                'data' => [
                    'type' => 'import',
                    'title' => __('inspirecms::pages/package.import_title'),
                ],
            ];
        }

        if (static::getExportResource()::canViewAny()) {
            $components[] = [
            'component' => ListPackages::class,
                'data' => [
                    'type' => 'export',
                    'title' => __('inspirecms::pages/package.export_title'),
                ],
            ];
        }

        return collect($components)->unique('data.type')->values()->all();
    }

    private static function getImportResource(): string
    {
        return InspireCmsConfig::getFilamentResource('import', ImportResource::class);
    }

    private static function getExportResource(): string
    {
        return InspireCmsConfig::getFilamentResource('export', ExportResource::class);
    }

    public function getTitle(): string | Htmlable
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::pages/package.title');
    }
}
