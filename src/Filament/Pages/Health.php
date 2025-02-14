<?php

namespace SolutionForest\InspireCms\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Factories\SitemapGeneratorFactory;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Filament\Contracts\GuardPage;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

// todo: need redo the layout
class Health extends Page implements ClusterSectionPage, GuardPage, HasActions, HasForms
{
    use ClusterSectionPageTrait;
    use InteractsWithActions;
    use InteractsWithForms;

    public static string $view = 'inspirecms::filament.pages.health';

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $cluster = Settings::class;

    public static function getPermissionName(): string
    {
        return 'view_health';
    }

    public static function getPermissionDisplayName(): string
    {
        return __('inspirecms::pages/health.title');
    }

    public function getBreadcrumbs(): array
    {
        return [
            ...parent::getBreadcrumbs(),
            static::getNavigationLabel(),
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::pages/health.title');
    }

    public function getStatusInfo(): array
    {
        $permissions = $this->getPermissionsStatusData();
        $sitemap = $this->getSiteMapStatusData();

        return [
            'permissions' => [
                'title' => __('inspirecms::pages/health.permissions.label'),
                ...$permissions,
                'action' => 'fix',
            ],
            'sitemap' => [
                'title' => __('inspirecms::pages/health.sitemap.label'),
                ...$sitemap,
                'action' => 'fix',
            ],
        ];
    }

    public function fixAction(): Action
    {
        return Action::make('fix')
            ->label(__('inspirecms::pages/health.actions.fix.label'))
            ->outlined()
            ->size('sm')
            ->action(function (array $arguments) {

                $needRefresh = false;

                switch ($arguments['action']) {
                    case 'permissions':
                        $needRefresh = $this->fixPermissions();

                        break;

                    case 'sitemap':
                        $needRefresh = $this->fixSiteMap();

                        break;

                }

                if ($needRefresh) {
                    $this->dispatch('$refresh');
                }
            });
    }

    protected function getPermissionsStatusData(): array
    {
        $permissions = $this->getAllPermissions();

        $missing = $this->getMissingPermissions($permissions);

        return [
            'status' => $this->formateStatusData(count($permissions), count($missing), count($missing) == 0),
            'data' => $this->formateStatusContent([
                'Missing permissions' => array_values($missing),
            ]),
        ];
    }

    private function getMissingPermissions(array $permissions = []): array
    {
        // check permissions exist
        $permissionModel = InspireCmsConfig::getPermissionModelClass();
        $existingPermissions = $permissionModel::whereIn('name', $permissions)->whereGuardName(InspireCmsConfig::getGuardName())->pluck('name')->toArray();

        return array_diff($permissions, $existingPermissions);
    }

    protected function getSiteMapStatusData(): array
    {
        // Determin the sitemap is generated or not
        $fullFilePath = InspireCmsConfig::get('content.sitemap.file_path');

        return [
            'status' => $this->formateStatusData(1, file_exists($fullFilePath) ? 0 : 1, file_exists($fullFilePath)),
            'data' => $this->formateStatusContent([
                'Missing sitemap file',
            ]),
        ];
    }

    protected function fixPermissions(): bool
    {
        $missing = $this->getMissingPermissions($this->getAllPermissions());

        if (empty($missing)) {
            return false;
        }

        PermissionHelper::setupPermissions();

        return true;
    }

    protected function fixSiteMap(): bool
    {
        try {
            // call the sitemap generator
            $generator = SitemapGeneratorFactory::create();

            $generator->generateSitemapFile();
        } catch (\Throwable $th) {

            Notification::make()
                ->danger()
                ->title($th->getMessage())
                ->send();

            return false;
        }

        return true;
    }

    protected function formateStatusData($total, $invalid, $valid): array
    {
        return [
            'total' => $total,
            'invalid' => $invalid,
            'isHealthy' => $valid,
        ];
    }

    protected function formateStatusContent(array $invalidItems): array
    {
        return [
            'invalidMessage' => $invalidItems,
        ];
    }

    private function getAllPermissions(): array
    {
        return PermissionManifest::permissions()->unique()->sort()->values()->all();
    }
}
