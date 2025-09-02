<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Filament\Resources\Contents\Schemas\Components\ContentPropertyDataGroup;

class ContentPreviewBuilderEditorForm
{
    public static function configure(Schema $schema): Schema
    {
        $availableLanguages = inspirecms()->getAllAvailableLanguages();
        $langs = collect($availableLanguages)
            ->mapWithKeys(fn (LanguageDto $lang) => [$lang->code => $lang->getLabel()])
            ->all();

        return $schema
            ->components([
                Select::make('activeLocale')
                    ->options($langs)
                    ->afterStateHydrated(fn ($component) => $component->state(array_key_first($langs)))
                    ->selectablePlaceholder(false)
                    ->prefixIcon(FilamentIcon::resolve('inspirecms::language'))
                    ->hiddenLabel()
                    ->suffix(function ($state) use ($availableLanguages) {
                        if (! $state) {
                            return null;
                        }
                        $lang = collect($availableLanguages)->get($state);
                        if (! $lang || ! $lang?->isDefault) {
                            return null;
                        }

                        return __('inspirecms::inspirecms.default');
                    })
                    ->live(),
                ContentPropertyDataGroup::make(),
            ]);
    }
}
