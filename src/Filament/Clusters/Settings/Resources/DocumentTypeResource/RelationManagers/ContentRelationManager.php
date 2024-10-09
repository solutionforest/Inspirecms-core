<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentRelationManager extends RelationManager
{
    protected static string $relationship = 'content';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('parent'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title')),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('inspirecms::inspirecms.slug'))
                    ->badge(),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label(__('inspirecms::inspirecms.parent')),
            ])
            ->recordUrl(fn ($record) => $this->getRecordUrl($record))
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label(__('inspirecms::actions.open.label'))
                    ->icon(FilamentIcon::resolve('inspirecms::goto'))
                    ->iconPosition(IconPosition::After)
                    ->url(fn ($record) => $this->getRecordUrl($record))
                    ->visible(fn (Tables\Actions\Action $action) => filled($action->getUrl())),
            ]);
    }

    protected function getRecordUrl(Content $record): ?string
    {
        $resource = config('inspirecms.filament.resources.page', PageResource::class);

        return FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view'], ['record' => $record], false);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::inspirecms.referenced_by_xxx', [
            'name' => parent::getTitle($ownerRecord, $pageClass),
        ]);
    }
}
