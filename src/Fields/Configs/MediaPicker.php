<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Converter;
use SolutionForest\InspireCms\Fields\Converters\MediaPickerConverter;
use SolutionForest\InspireCms\Support\MediaLibrary\FilterType;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\MediaPicker as MediaPickerComponent;

#[ConfigName('mediaPicker', 'Media Picker', 'Picker', 'heroicon-o-photo')]
#[FormComponent(MediaPickerComponent::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
#[Converter(MediaPickerConverter::class)]
class MediaPicker extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public array $types = [];

    public ?int $min = null;

    public ?int $max = null;

    public function getFormSchema(): array
    {
        return [
            Tabs::make('tabs')
                ->tabs([
                    Tab::make('Validation')
                        ->schema([
                            TextInput::make('min')->numeric(),
                            TextInput::make('max')->numeric(),
                        ]),
                    Tab::make('Presentation')
                        ->schema([
                            Select::make('types')
                                ->inlineLabel()
                                ->placeholder(__('inspirecms-support::media-library.filter.type.placeholder'))
                                ->options(FilterType::class)
                                ->multiple(),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Component $component): void
    {
        if ($component instanceof MediaPickerComponent) {
            $component->filterTypes($this->types);
            if ($this->min) {
                $component->min($this->min);
            }
            if ($this->max) {
                $component->max($this->max);
            }
        }
    }
}
