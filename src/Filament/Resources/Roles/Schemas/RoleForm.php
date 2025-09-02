<?php

namespace SolutionForest\InspireCms\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Forms\Components\TieredPermissionsRepeater;
use SolutionForest\InspireCms\Filament\Resources\Roles\Schemas\Components\RoleNameInput;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        RoleNameInput::make(),
                        Hidden::make('guard_name')
                            ->dehydratedWhenHidden(true)
                            ->dehydrateStateUsing(fn () => AuthHelper::guardName()),
                    ]),
                Group::make()
                    ->statePath('permissions')
                    ->columnSpanFull()->columns(1)
                    ->schema([
                        Section::make()
                            ->heading(__('inspirecms::resources/role.sections.cluster_section_access.heading'))
                            ->description(__('inspirecms::resources/role.sections.cluster_section_access.description'))
                            ->collapsible()
                            ->columns(2)
                            ->schema([static::getFormComponentForClusterSection()]),
                        Section::make()
                            ->heading(__('inspirecms::resources/role.sections.action_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.action_permissions.description'))
                            ->collapsible()
                            ->schema([static::getFormComponentForActionSection()]),
                        Section::make()
                            ->heading(__('inspirecms::resources/role.sections.widget_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.widget_permissions.description'))
                            ->collapsible()
                            ->schema([static::getFormComponentForWidgetSection()]),
                        Section::make()
                            ->heading(__('inspirecms::resources/role.sections.page_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.page_permissions.description'))
                            ->collapsible()
                            ->schema([static::getFormComponentForPageSection()]),
                        Section::make()
                            ->heading(__('inspirecms::resources/role.sections.resource_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.resource_permissions.description'))
                            ->collapsible()
                            ->statePath('resource_permissions')
                            ->schema(static::getFormComponentForResourcePermissionsSection()),
                        Section::make()
                            ->heading(__('inspirecms::resources/role.sections.tiered_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.tiered_permissions.description'))
                            ->collapsible()
                            ->statePath('tiered_permissions')
                            ->schema(static::getFormComponentForTieredPermissionsSection()),
                    ])
                    ->afterStateHydrated(function (null | Role | RoleContract $record, Group $component) {

                        $permissionNames = $record ? $record->permissions->pluck('name') : [];
                        $state = [];

                        $resourcePermissions = PermissionManifest::getResourcePermissions();
                        $tieredPermissions = PermissionManifest::getTieredPermissions();

                        $clusterSectionPermissions = PermissionManifest::getClusterSectionPermissions();
                        $wrapedResourcePermissions = collect($resourcePermissions)->collapse()->all();
                        $actionPermissions = PermissionManifest::getActionPermissions();
                        $widgetPermissions = PermissionManifest::getWidgetPermissions();
                        $pagePermissions = PermissionManifest::getPagePermissions();
                        $wrapedTieredPermissions = collect($tieredPermissions)->collapse()->keys()->all();

                        $getModelPermissionKey = fn ($model) => Str::lower($model);

                        foreach ($permissionNames as $permissionName) {

                            $permissionParts = explode('.', $permissionName);
                            $modelPermissionKey = $getModelPermissionKey($permissionParts[0]);

                            if (count($permissionParts) == 3 && in_array(implode('.', Arr::only($permissionParts, [0, 1])), $wrapedTieredPermissions)) {
                                $state['tiered_permissions'][$modelPermissionKey][] = $permissionName;

                                continue;
                            }

                            if (array_key_exists($permissionName, $clusterSectionPermissions)) {
                                $state['cluster_section_access'][] = $permissionName;

                                continue;
                            }

                            if (array_key_exists($permissionName, $actionPermissions)) {
                                $state['action_permissions'][] = $permissionName;

                                continue;
                            }

                            if (array_key_exists($permissionName, $widgetPermissions)) {
                                $state['widget_permissions'][] = $permissionName;

                                continue;
                            }

                            if (array_key_exists($permissionName, $pagePermissions)) {
                                $state['page_permissions'][] = $permissionName;

                                continue;
                            }

                            if (array_key_exists($permissionName, $wrapedResourcePermissions) && count($permissionParts) > 1) {
                                $state['resource_permissions'][$modelPermissionKey][] = $permissionName;

                                continue;
                            }
                        }

                        // Ensure all keys are set for CheckboxList components
                        foreach (['cluster_section_access', 'action_permissions', 'page_permissions', 'widget_permissions'] as $key) {
                            if (! isset($state[$key])) {
                                $state[$key] = [];
                            }
                        }
                        foreach (['resource_permissions' => $resourcePermissions, 'tiered_permissions' => $tieredPermissions] as $group => $groupedModelPermissions) {
                            foreach (array_map(fn ($n) => Str::lower($n), array_keys($groupedModelPermissions)) as $modelLabelForResource) {
                                $modelPermissionKey = $getModelPermissionKey($modelLabelForResource);
                                if (! isset($state[$group][$modelPermissionKey])) {
                                    $state[$group][$modelPermissionKey] = [];
                                }
                            }
                        }

                        $component->state($state);
                    })
                    ->dehydrated(false) // handle on `saveRelationshipsUsing`
                    ->saveRelationshipsUsing(function (Role | RoleContract $record, array $state) {
                        $permissionNames = collect($state)
                            ->map(function ($permissions, $group) {
                                if ($group == 'tiered_permissions') {
                                    return PermissionHelper::ensureTieredPermissions($permissions);
                                } elseif ($group == 'resource_permissions') {
                                    return collect($permissions)
                                        ->collapse()
                                        ->all();
                                }

                                return $permissions;
                            })
                            ->collapse()
                            ->all();
                        $record->syncPermissions($permissionNames);
                    }),
            ]);
    }

    protected static function getFormComponentForClusterSection($name = 'cluster_section_access')
    {
        return static::getCommonCheckboxListForSection($name, __('inspirecms::resources/role.cluster_section_access.validation_attribute'), PermissionManifest::getClusterSectionPermissions());
    }

    protected static function getFormComponentForResourcePermissionsSection()
    {
        $modelPermissions = PermissionManifest::getResourcePermissions();

        $components = [];

        foreach ($modelPermissions as $model => $resourcePermissionOptions) {

            $key = Str::lower($model);
            $components[] = Section::make()
                ->aside()
                ->heading($model)
                ->schema([
                    static::getCommonCheckboxListForSection($key, __('inspirecms::resources/role.resource_permissions.validation_attribute'), $resourcePermissionOptions)
                        ->columnSpanFull()->columns([
                            'default' => 2,
                            'md' => 2,
                            'lg' => 3,
                        ]),
                ]);

        }

        return $components;
    }

    /**
     * @return array
     */
    protected static function getFormComponentForTieredPermissionsSection()
    {
        $tieredPermissions = PermissionManifest::getTieredPermissions();
        $components = [];

        foreach ($tieredPermissions as $label => $items) {

            $model = PermissionManifest::getModelForTieredPermission($label);
            if (! $model) {
                continue;
            }

            $key = Str::lower($label);
            $components[] = Section::make()
                ->aside()
                ->heading($label)
                ->schema([
                    TieredPermissionsRepeater::make($key)
                        ->hiddenLabel()
                        ->tieredModel($model)
                        ->permissions($items),
                ]);
        }

        return $components;
    }

    protected static function getFormComponentForActionSection($name = 'action_permissions')
    {
        return static::getCommonCheckboxListForSection($name, __('inspirecms::resources/role.action_permissions.validation_attribute'), PermissionManifest::getActionPermissions());
    }

    protected static function getFormComponentForWidgetSection($name = 'widget_permissions')
    {
        return static::getCommonCheckboxListForSection($name, __('inspirecms::resources/role.widget_permissions.validation_attribute'), PermissionManifest::getWidgetPermissions());
    }

    protected static function getFormComponentForPageSection($name = 'page_permissions')
    {
        return static::getCommonCheckboxListForSection($name, __('inspirecms::resources/role.page_permissions.validation_attribute'), PermissionManifest::getPagePermissions());
    }

    private static function getCommonCheckboxListForSection($name, $attribute, $options)
    {
        return CheckboxList::make($name)
            ->validationAttribute($attribute)
            ->hiddenLabel()
            ->searchable()
            ->options($options)
            ->bulkToggleable()
            ->gridDirection('row')
            ->columnSpanFull()->columns([
                'default' => 2,
                'md' => 2,
                'lg' => 4,
            ]);
    }
}
