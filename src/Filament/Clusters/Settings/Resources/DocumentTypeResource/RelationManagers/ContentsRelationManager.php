<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Models\Contracts\Content;
use Termwind\Components\Element;

class ContentsRelationManager extends RelationManager
{
    protected static string $relationship = 'contents';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title')),
            ])
            ->recordUrl(fn ($record) => $this->getRecordUrl($record))
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label(__('inspirecms::actions.open.label'))
                    ->url(fn ($record) => $this->getRecordUrl($record))
                    ->visible(fn (Tables\Actions\Action $action) => filled($action->getUrl())),
            ]);
    }

    protected function getRecordUrl(Content $record): ?string
    {
        try {

            $url = null;

            $resource = $this->getOwnerRecord()->is_element_type
                ? config('inspirecms.resources.element', ElementResource::class)
                : config('inspirecms.resources.page', PageResource::class);

            foreach (['view', 'edit'] as $action) {

                if (filled($url)) {
                    continue;
                }

                $url = $resource::getUrl('edit', ['record' => $record]);
            }
        } catch (\Throwable $th) {
            return null;
        }

        return $url;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::inspirecms.referenced_by_xxx', [
            'name' => parent::getTitle($ownerRecord, $pageClass),
        ]);
    }
}
