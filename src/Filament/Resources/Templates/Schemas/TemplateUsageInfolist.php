<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class TemplateUsageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label(__('inspirecms::inspirecms.id')),

                TextEntry::make('slug')
                    ->label(__('inspirecms::resources/template.slug.label'))
                    ->badge(),

                RepeatableEntry::make('documentTypes')
                    ->label(__('inspirecms::inspirecms.document_type.plural'))
                    ->contained(false)
                    ->schema([
                        TextEntry::make('slug')
                            ->hiddenLabel()
                            ->icon(fn ($record) => $record->icon)
                            ->url(function ($record, $state) {
                                return $record instanceof Model ?
                                    FilamentResourceHelper::attemptToGetUrl(
                                        InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class),
                                        ['edit', 'view'],
                                        ['record' => $record],
                                        false
                                    ) : null;
                            }, true),
                    ]),

                RepeatableEntry::make('contents')
                    ->label(__('inspirecms::inspirecms.content.plural'))
                    ->contained(false)
                    ->schema([
                        TextEntry::make('slug')
                            ->hiddenLabel()
                            ->url(function ($record, $state) {
                                return $record instanceof Model ?
                                    FilamentResourceHelper::attemptToGetUrl(
                                        InspireCmsConfig::getFilamentResource('content', ContentResource::class),
                                        ['edit', 'view'],
                                        ['record' => $record],
                                        false
                                    ) : null;
                            }, true),
                    ]),
            ]);
    }
}
