<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Support\MediaLibrary\FilterType;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\MediaPicker as MediaPickerComponent;

#[ConfigName('mediaPicker', 'Media Picker', 'Picker', 'heroicon-o-pencil')]
#[FormComponent(MediaPickerComponent::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
class MediaPicker extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public array $types = [];

    public ?int $min = null;

    public ?int $max = null;

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make('tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Validation')
                        ->schema([
                            Forms\Components\TextInput::make('min')->numeric(),
                            Forms\Components\TextInput::make('max')->numeric(),
                        ]),
                    Forms\Components\Tabs\Tab::make('Presentation')
                        ->schema([
                            Forms\Components\Select::make('types')
                                ->inlineLabel()
                                ->placeholder(__('inspirecms-support::media-library.filter.type.placeholder'))
                                ->options(FilterType::class)
                                ->multiple(),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
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
