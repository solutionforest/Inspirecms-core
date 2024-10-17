<?php

namespace SolutionForest\InspireCms\FieldTypes\Configs;

use Filament\Forms;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Support\Forms\Components\MediaPicker as MediaPickerComponent;

#[ConfigName('mediaPicker', 'Media Picker', 'Picker', 'heroicon-o-pencil')]
#[FormComponent(MediaPickerComponent::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
class MediaPicker extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public array $types = [];

    public bool $multiple = false;

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make('tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Presentation')
                        ->schema([
                            Forms\Components\Select::make('types')
                                ->inlineLabel()
                                ->placeholder(__('inspirecms-support::media-library.filter.type.placeholder'))
                                ->options(__('inspirecms-support::media-library.filter.type.options'))
                                ->multiple(),
                            Forms\Components\Toggle::make('multiple')
                                ->default(false),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
    {
        if ($component instanceof MediaPickerComponent) {
            $component->filterTypes($this->types);
            $component->multiple($this->multiple);
        }
    }
}
