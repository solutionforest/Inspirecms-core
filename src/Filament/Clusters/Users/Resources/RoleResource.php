<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Clusters;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\InspireCmsConfig;
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
                            ->heading(__('inspirecms::resources/role.cluster_section_access.section.heading'))
                            ->description(__('inspirecms::resources/role.cluster_section_access.section.description'))
                            ->collapsible()
                            ->statePath('cluster_section_access')
                            ->columns(2)
                            ->schema(static::getFormComponentsForClusterSection()),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::resources/role.action_permissions.section.heading'))
                            ->description(__('inspirecms::resources/role.action_permissions.section.description'))
                            ->collapsible()
                            ->statePath('action_permissions')
                            ->schema(static::getFormComponentsForActionPermissionsSection()),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::resources/role.page_permissions.section.heading'))
                            ->description(__('inspirecms::resources/role.page_permissions.section.description'))
                            ->collapsible()
                            ->statePath('page_permissions')
                            ->schema(static::getFormComponentsForPagePermissionsSection()),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::resources/role.resource_permissions.section.heading'))
                            ->description(__('inspirecms::resources/role.resource_permissions.section.description'))
                            ->collapsible()
                            ->statePath('resource_permissions')
                            ->schema(static::getFormComponentForResourcePermissionsSection()),
                    ])
                    ->afterStateHydrated(function (null | Role | RoleContract $record, Forms\Components\Group $component) {
                        if (is_null($record)) {
                            $component->state([]);

                            return;
                        }

                        $permissionNames = $record->permissions->pluck('name');
                        $state = [];
                        $clusterSectionPermissions = PermissionManifest::getClusterSectionPermissions();
                        $resourcePermissions = collect(PermissionManifest::getClusterSectionResourceModelPermissions())->collapse()->all();
                        $actionPermissions = PermissionManifest::getActionPermissions();
                        $pagePermissions = PermissionManifest::getPagePermissions();

                        foreach ($permissionNames as $permissionName) {

                            if (array_key_exists($permissionName, $clusterSectionPermissions)) {
                                $state['cluster_section_access'][$permissionName] = true;

                                continue;
                            }

                            if (array_key_exists($permissionName, $actionPermissions)) {
                                $state['action_permissions'][$permissionName] = true;

                                continue;
                            }

                            if (array_key_exists($permissionName, $pagePermissions)) {
                                $state['page_permissions'][$permissionName] = true;

                                continue;
                            }

                            if (array_key_exists($permissionName, $resourcePermissions)) {
                                $state['resource_permissions'][$permissionName] = true;

                                continue;
                            }
                        }

                        $component->state($state);
                    })
                    ->dehydrated(false) // handle on `saveRelationshipsUsing`
                    ->saveRelationshipsUsing(function (Role | RoleContract $record, array $state) {
                        $permissionNames = collect($state)->collapse()->filter()->keys()->all();
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
                Tables\Actions\EditAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/edit/{record}'),
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
        return __('inspirecms::inspirecms.role');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('permissions')
            ->where('guard_name', InspireCmsConfig::getGuardName());
    }

    //region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    //endregion Global search

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::resources/role.name.label'))
            ->validationAttribute(__('inspirecms::resources/role.name.validation_attribute'))
            ->unique(table: static::getModel(), column: 'name', ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                return $rule->where('guard_name', InspireCmsConfig::getGuardName());
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
            ->dehydrateStateUsing(fn () => InspireCmsConfig::getGuardName());
    }

    /**
     * @return array
     */
    protected static function getFormComponentsForClusterSection()
    {
        return collect(PermissionManifest::getClusterSectionPermissions())
            ->map(fn ($label, $value) => Forms\Components\Toggle::make($value)->label($label))
            ->all();
    }

    /**
     * @return array
     */
    protected static function getFormComponentForResourcePermissionsSection()
    {
        $modelPermissions = PermissionManifest::getClusterSectionResourceModelPermissions();

        $components = [];

        foreach ($modelPermissions as $model => $resourcePermissionOptions) {

            $components[] = Forms\Components\Section::make()
                ->heading($model)
                ->schema(
                    collect($resourcePermissionOptions)
                        ->map(fn ($label, $value) => Forms\Components\Toggle::make($value)->label($label))
                        ->all()
                )
                ->aside()
                ->columnSpanFull()->columns([
                    'default' => 2,
                    'md' => 2,
                    'lg' => 3,
                ]);

        }

        return $components;
    }

    /**
     * @return array
     */
    protected static function getFormComponentsForActionPermissionsSection()
    {
        return collect(PermissionManifest::getActionPermissions())
            ->map(fn ($label, $value) => Forms\Components\Toggle::make($value)->label($label))
            ->all();
    }

    /**
     * @return array
     */
    protected static function getFormComponentsForPagePermissionsSection()
    {
        return collect(PermissionManifest::getPagePermissions())
            ->map(fn ($label, $value) => Forms\Components\Toggle::make($value)->label($label))
            ->all();
    }
    //endregion Form field(s)/component(s)
}
