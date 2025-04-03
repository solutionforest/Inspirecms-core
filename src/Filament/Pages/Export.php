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
use Livewire\Attributes\Url;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Filament\Resources\ExportResource;
use SolutionForest\InspireCms\Filament\Resources\ImportResource;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Livewire\ListImportNExport;

class Export extends Page implements ClusterSectionPage, HasActions, HasForms, HasInfolists
{
    use ClusterSectionPageTrait {
        ClusterSectionPageTrait::canAccess as traitCanAccess;
    }
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithInfolists;

    public static string $view = 'inspirecms::filament.pages.export';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $cluster = Settings::class;

    #[Url]
    public ?string $redirectUrl = null;

    public function mountCanAuthorizeAccess(): void
    {
        // Overwrite the default behavior
        if (! static::canAccess()) {

            if (filled($this->redirectUrl)) {
                redirect()->intended($this->redirectUrl);
            } else {
                abort(403);
            }
        }
    }

    public static function canAccess(): bool
    {
        return static::getImportResource()::canViewAny() || static::getExportResource()::canViewAny();
    }

    public function getTableComponents(): array
    {
        $components = [];

        if (static::getImportResource()::canViewAny()) {
            $components[] = [
                'component' => ListImportNExport::class,
                'data' => [
                    'type' => 'import',
                    'title' => __('inspirecms::pages/export.import_title'),
                ],
            ];
        }

        if (static::getExportResource()::canViewAny()) {
            $components[] = [
                'component' => ListImportNExport::class,
                'data' => [
                    'type' => 'export',
                    'title' => __('inspirecms::pages/export.export_title'),
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
        return __('inspirecms::pages/export.title');
    }
}
