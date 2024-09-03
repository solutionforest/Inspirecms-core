<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use SolutionForest\InspireCms\Filament\Clusters\Users;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\RoleResource\Pages;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $cluster = Users::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getNameFormComponent(),
                static::getGuardNameFormComponent(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return config('permission.models.role', Role::class);
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.role');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('guard_name', static::getGuardName());
    }

    public static function getGuardName(): string
    {
        return config('inspirecms.auth.guard', 'inspirecms');
    }

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::inspirecms.name'))
            ->unique(table: static::getModel(), column: 'name', ignoreRecord: true, modifyRuleUsing: function (Unique $rule) {
                return $rule->where('guard_name', static::getGuardName());
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
            ->dehydrateStateUsing(fn () => static::getGuardName());
    }
    //endregion Form field(s)/component(s)
}
