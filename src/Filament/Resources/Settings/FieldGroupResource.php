<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings;

use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroupResource as BaseResource;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\Pages;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\RelationManagers;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class FieldGroupResource extends BaseResource
{
    protected static ?int $navigationSort = -9;

    protected static ?string $navigationIcon = null;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Tabs::make()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('inspirecms::inspirecms.general'))
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('inspirecms::inspirecms.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($operation, $state, Forms\Get $get, Forms\Set $set) {
                                        // Fill slug if empty / operation is create
                                        if ($operation === 'create' || empty($get('name'))) {
                                            $set('name', Str::slug($state, '_'));
                                        }
                                    }),
                                Forms\Components\TextInput::make('name')
                                    ->label(__('inspirecms::inspirecms.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state, '_')))
                                    ->unique(ignoreRecord: true),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('inspirecms::inspirecms.setting'))
                            ->schema([
                                Forms\Components\Toggle::make('active')
                                    ->label(__('inspirecms::inspirecms.is_active'))
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFieldGroup::route('/'),
            'edit' => Pages\EditFieldGroup::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FieldsRelationManager::class,
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getFieldGroupModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.field_group');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms::inspirecms.setting');
    }
}
