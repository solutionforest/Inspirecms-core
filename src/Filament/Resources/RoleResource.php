<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Clusters;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\TieredPermissionsRepeater;
use SolutionForest\InspireCms\Filament\Resources\RoleResource\Pages;
use SolutionForest\InspireCms\Filament\Resources\RoleResource\RelationManagers;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $cluster = Clusters\Users::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        static::getNameFormComponent(),
                        static::getGuardNameFormComponent(),
                    ]),
                Forms\Components\Group::make()
                    ->statePath('permissions')
                    ->columnSpanFull()->columns(1)
                    ->schema([
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::resources/role.sections.cluster_section_access.heading'))
                            ->description(__('inspirecms::resources/role.sections.cluster_section_access.description'))
                            ->collapsible()
                            ->columns(2)
                            ->schema([static::getFormComponentForClusterSection()]),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::resources/role.sections.action_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.action_permissions.description'))
                            ->collapsible()
                            ->schema([static::getFormComponentForActionSection()]),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::resources/role.sections.widget_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.widget_permissions.description'))
                            ->collapsible()
                            ->schema([static::getFormComponentForWidgetSection()]),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::resources/role.sections.page_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.page_permissions.description'))
                            ->collapsible()
                            ->schema([static::getFormComponentForPageSection()]),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::resources/role.sections.resource_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.resource_permissions.description'))
                            ->collapsible()
                            ->statePath('resource_permissions')
                            ->schema(static::getFormComponentForResourcePermissionsSection()),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::resources/role.sections.tiered_permissions.heading'))
                            ->description(__('inspirecms::resources/role.sections.tiered_permissions.description'))
                            ->collapsible()
                            ->statePath('tiered_permissions')
                            ->schema(static::getFormComponentForTieredPermissionsSection()),
                    ])
                    ->afterStateHydrated(function (null | Role | RoleContract $record, Forms\Components\Group $component) {

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::resources/role.name.label'))
                    ->badge(),
                Tables\Columns\TextColumn::make('allow_clusters')
                    ->label(__('inspirecms::resources/role.cluster_section_access.label'))
                    ->getStateUsing(function (Role $record) {
                        $clusterSectionPermissions = PermissionManifest::getClusterSectionPermissions();
                        $allowSections = $record->getPermissionNames()
                            ->intersect(array_keys($clusterSectionPermissions))
                            ->map(fn ($permissionName) => $clusterSectionPermissions[$permissionName]);

                        return $allowSections->implode(', ');
                    })
                    ->limit(50),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'users' => RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getRoleModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.role.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inspirecms::inspirecms.role.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('permissions')
            ->where('guard_name', AuthHelper::guardName());
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    // endregion Global search

    // region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::resources/role.name.label'))
            ->validationAttribute(__('inspirecms::resources/role.name.validation_attribute'))
            ->unique(table: static::getModel(), column: 'name', ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                return $rule->where('guard_name', AuthHelper::guardName());
            })
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getGuardNameFormComponent()
    {
        return Forms\Components\Hidden::make('guard_name')
            ->dehydratedWhenHidden(true)
            ->dehydrateStateUsing(fn () => AuthHelper::guardName());
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
            $components[] = Forms\Components\Section::make()
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
            $components[] = Forms\Components\Section::make()
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
        return Forms\Components\CheckboxList::make($name)
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
    // endregion Form field(s)/component(s)

    public static function canCreate(): bool
    {
        if (! app(LicenseManager::class)->canCreateRole()) {
            return false;
        }

        return parent::canCreate();
    }
}
