<?php

namespace SolutionForest\InspireCms\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use Spatie\Permission\PermissionRegistrar;

// todo: add permission check to access this page
// todo: need redo the layout
class Health extends Page implements ClusterSectionPage, HasForms, HasActions
{
    use ClusterSectionPageTrait;
    use InteractsWithActions;
    use InteractsWithForms;

    public static string $view = 'inspirecms::filament.pages.health';

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $cluster = Settings::class;

    public function getBreadcrumbs(): array
    {
        return [
            ...parent::getBreadcrumbs(),
            static::getNavigationLabel(),
        ];
    }

    public function getStatusInfo(): array
    {
        return [
            'permissions' => [
                'title' => __('inspirecms::health.permissions.label'),
                'status' => $this->getPermissionsStatus(),
                'action' => 'fix',
            ],
        ];
    }

    public function fixAction(): Action
    {
        return Action::make('fix')
            ->label(__('inspirecms::health.actions.fix.label'))
            ->outlined()
            ->action(function (array $arguments) {
                switch ($arguments['action']) {
                    case 'permissions':
                        $this->resolvePermissions();
                        $this->dispatch('$refresh');
                        break;
                }
            });
    }

    protected function getPermissionsStatus(): array
    {
        $permissions = $this->getAllPermissions();

        // check permissions exist
        $permissionModel = app(PermissionRegistrar::class)->getPermissionClass();
        $existingPermissions = $permissionModel::whereIn('name', $permissions)->whereGuardName(InspireCmsConfig::getGuardName())->pluck('name')->toArray();

        $missing = array_diff($permissions, $existingPermissions);
        // $valid = array_intersect($permissions, $existingPermissions);

        return [
            ...$this->formateStatusData(count($permissions), count($missing), count($missing) == 0),
            'missing' => array_values($missing),
        ];
    }

    protected function resolvePermissions()
    {
        $missins = $this->getPermissionsStatus()['missing'] ?? [];

        if (empty($missins)) {
            return;
        }

        $permissionModel = app(PermissionRegistrar::class)->getPermissionClass();
        
        foreach ($missins as $permission) {
            $permissionModel::findOrCreate($permission, InspireCmsConfig::getGuardName());
        }


        $this->dispatch('$refresh');
    }

    protected function formateStatusData($total, $invalid, $valid): array
    {
        return [
            'total' => $total,
            'invalid' => $invalid,
            'isHealthy' => $valid,
        ];
    }

    private function getAllPermissions(): array
    {
        return collect([
            ...array_keys(PermissionManifest::getClusterSectionPermissions()),
            ...collect(PermissionManifest::getClusterSectionResourceModelPermissions())
                ->flatMap(fn ($arr) => array_keys($arr))
                ->all(),
        ])->unique()->sort()->values()->all();
    }
}
