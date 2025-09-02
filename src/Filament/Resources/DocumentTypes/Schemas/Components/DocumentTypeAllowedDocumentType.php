<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Schemas\Components;

use Filament\Forms\Components\ModalTableSelect;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Tables\DocumentTypesTable;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypeAllowedDocumentType
{
    public static function make()
    {
        return ModalTableSelect::make('allowedDocumentTypes')
            ->relationship('allowedDocumentTypes', 'slug')
            ->label(__('inspirecms::resources/document-type.allowed_document_types.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.allowed_document_types.validation_attribute'))
            ->tableConfiguration(DocumentTypesTable::class)
            ->tableArguments(fn ($record) => ['fromModalTableSelect' => true, 'fromDocumentType' => $record])
            ->multiple()
            ->getOptionLabelsUsing(function (array $values) {
                $modelFqcn = InspireCmsConfig::getDocumentTypeModelClass();
                $pkColumn = app($modelFqcn)->getKeyName();

                return collect($modelFqcn::whereKey($values)->pluck('slug', $pkColumn))
                    ->sortBy(function ($label, $key) use ($values) {
                        return array_search($key, $values);
                    })
                    ->map(function ($label, $pk) {
                        $url = FilamentResourceHelper::attemptToGetUrl(
                            InspireCmsConfig::getFilamentResource('documentType', DocumentTypeResource::class),
                            ['edit', 'view'],
                            ['record' => $pk],
                            false,
                        );
                        $icon = FilamentIcon::resolve('inspirecms::goto');
                        $urlHtml = UIHelper::generateLink($label, $url, ['target' => '_blank', 'rel' => 'noopener noreferrer']);

                        return UIHelper::generateTextWithIcon(
                            text: $urlHtml->toHtml(),
                            icon: $icon,
                            iconSize: 'sm',
                            iconPosition: 'after',
                        );
                    })
                    ->all();
            });
    }
}
