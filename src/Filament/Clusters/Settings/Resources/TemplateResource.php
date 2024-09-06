<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Models\Contracts\Template;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class TemplateResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -7;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $cluster = Settings::class;

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid(['md' => 2, 'lg' => 3])
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('name')
                        ->weight('bold')
                        ->description(fn (Template $record) => $record->path),
                ]),
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
}
