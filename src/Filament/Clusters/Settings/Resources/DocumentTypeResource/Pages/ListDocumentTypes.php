<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListDocumentTypes extends BaseListPage
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class);
    }

    public function form(Form $form): Form
    {
        return static::getResource()::createForm($form);
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
