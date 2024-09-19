<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;

class DocumentTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'documentTypes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::inspirecms.name')),
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

    protected function getRecordUrl($record): ?string
    {
        try {

            $url = null;

            foreach (['view', 'edit'] as $action) {

                if (filled($url)) {
                    continue;
                }

                $url = config('inspirecms.resources.document_type', DocumentTypeResource::class)::getUrl('edit', ['record' => $record]);
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
