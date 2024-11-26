<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplateResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -5;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $cluster = Settings::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'attach',
            'detach',
            'update_view',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(static::getNavigationLabel())
            ->columns([
                Tables\Columns\TextColumn::make('slug')->weight('bold'),
                Tables\Columns\TextColumn::make('path'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver()->modalWidth('5xl')->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplates::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getTemplateModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.template');
    }

    //region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    //endregion Global search
}
