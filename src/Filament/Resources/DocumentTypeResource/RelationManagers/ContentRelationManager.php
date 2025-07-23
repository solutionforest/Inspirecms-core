<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Filament\Tables\Actions\OpenAction;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentRelationManager extends RelationManager
{
    protected static string $relationship = 'content';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('parent'))
            ->modelLabel(__('inspirecms::inspirecms.content'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::resources/content.title.label')),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('inspirecms::resources/content.slug.label'))
                    ->badge(),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label(__('inspirecms::resources/content.parent.label')),
            ])
            ->recordUrl(fn ($record) => $this->getRecordUrl($record))
            ->actions([
                OpenAction::make()
                    ->url(fn ($record) => $this->getRecordUrl($record)),
            ]);
    }

    protected function getRecordUrl(Content $record): ?string
    {
        $resource = InspireCmsConfig::getFilamentResource('coantent', ContentResource::class);

        return FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view'], ['record' => $record], true);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::inspirecms.used_by_xxx', [
            'name' => __('inspirecms::inspirecms.content'),
        ]);
    }
}
