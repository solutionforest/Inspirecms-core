<?php

namespace SolutionForest\InspireCms\Filament\Pages;

use Filament\Pages\Page;
use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Url;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Filament\Resources\ExportResource;
use SolutionForest\InspireCms\Filament\Resources\ImportResource;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Livewire\ListImportNExport;

class Export extends Page implements ClusterSectionPage
{
    use ClusterSectionPageTrait {
        ClusterSectionPageTrait::canAccess as traitCanAccess;
    }

    public string $view = 'inspirecms::filament.pages.export';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-archive-box';

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

        return $components;
    }

    /**
     * @return class-string<resource>
     */
    protected static function getImportResource(): string
    {
        return InspireCmsConfig::getFilamentResource('import', ImportResource::class);
    }

    /**
     * @return class-string<resource>
     */
    protected static function getExportResource(): string
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
