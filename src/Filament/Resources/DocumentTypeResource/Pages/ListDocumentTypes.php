<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListDocumentTypes extends BaseListRecords
{
    public function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class);
    }

    // public function getTabs(): array
    // {
    //     return collect(\SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::cases())
    //         ->mapWithKeys(
    //             fn (\SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory $value) => [
    //                 $value->value => Tab::make()
    //                     ->label($value->getLabel())
    //                     ->modifyQueryUsing(fn ($query) => $query->where('type', $value->value)),
    //             ]
    //         )
    //         ->prepend(Tab::make(), 'all')
    //         ->toArray();
    // }
}
