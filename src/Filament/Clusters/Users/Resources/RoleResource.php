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
                            ->heading(__('inspirecms::permissions.assign_access.label'))
                            ->collapsible()
                            ->schema([
                                static::getFormComponentForClusterSection(),
                            ]),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::permissions.default_permissions.label'))
                            ->collapsible()
                            ->schema([
                                static::getFormComponentForDefaultPermissionsSection(),
                            ]),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::permissions.action_permissions.label'))
                            ->collapsible()
                            ->schema([
                                static::getFormComponentForActionPermissionsSection(),
                            ]),
                        Forms\Components\Section::make()
                            ->heading(__('inspirecms::permissions.page_permissions.label'))
                            ->collapsible()
                            ->schema([
                                static::getFormComponentForPagePermissionsSection(),
                            ]),
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

                        foreach ($permissionNames as $permissionName) {

                            if (array_key_exists($permissionName, $clusterSectionPermissions)) {
                                $state['cluster_section_access'][$permissionName] = true;

                                continue;
                            }

                            if (array_key_exists($permissionName, $resourcePermissions)) {
                                $state['default_permissions'][$permissionName] = true;

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
                    ->label(__('inspirecms::inspirecms.name'))
                    ->badge(),
                Tables\Columns\TextColumn::make('allow_sections')
                    ->label(__('inspirecms::inspirecms.allow_sections'))
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
            RelationManagers\UsersRelationManager::class,
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
            ->label(__('inspirecms::inspirecms.name'))
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
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getFormComponentForClusterSection()
    {
        return Forms\Components\Section::make()
            ->heading(__('inspirecms::permissions.cluster_section_access.label'))
            ->description(__('inspirecms::permissions.cluster_section_access.helper_text'))
            ->statePath('cluster_section_access')
            ->schema(
                collect(PermissionManifest::getClusterSectionPermissions())
                    ->map(fn ($label, $value) => Forms\Components\Toggle::make($value)->label($label))
                    ->all()
            )
            ->compact()
            ->aside()
            ->columnSpanFull()->columns(1);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getFormComponentForDefaultPermissionsSection()
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
                ->compact()
                ->aside()
                ->columnSpanFull()->columns([
                    'default' => 2,
                    'md' => 2,
                    'lg' => 3,
                    'xl' => 4,
                ]);

        }

        return Forms\Components\Group::make()
            ->statePath('default_permissions')
            ->schema($components);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getFormComponentForActionPermissionsSection()
    {
        return Forms\Components\Group::make()
            ->statePath('action_permissions')
            ->schema(
                collect(PermissionManifest::getActionPermissions())
                    ->map(fn ($label, $value) => Forms\Components\Toggle::make($value)->label($label))
                    ->all()
            )
            ->columnSpanFull()->columns(1);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getFormComponentForPagePermissionsSection()
    {
        return Forms\Components\Group::make()
            ->statePath('page_permissions')
            ->schema(
                collect(PermissionManifest::getPagePermissions())
                    ->map(fn ($label, $value) => Forms\Components\Toggle::make($value)->label($label))
                    ->all()
            )
            ->columnSpanFull()->columns(1);
    }
    //endregion Form field(s)/component(s)
}
