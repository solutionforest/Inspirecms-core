<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Concerns\HasAffixes;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Concerns;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Concerns\HasRules;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;

#[ConfigName('tags', 'Tags', 'List', 'heroicon-o-tag')]
#[FormComponent(TagsInput::class)]
#[DbType('mysql', 'text')]
#[DbType('sqlite', 'text')]
class Tags extends FieldTypeBaseConfig implements FieldTypeConfig
{
    use Concerns\HasAffixes;
    use HasRules;

    public ?string $separator = null;

    public ?array $suggestions = null;

    public ?bool $reorderable = null;

    public ?string $color = null;

    public ?string $suffix = null;

    public ?string $prefix = null;

    public function getFormSchema(): array
    {
        return [
            Tabs::make('tabs')
                ->tabs([
                    Tab::make('Validation')
                        ->schema([
                            static::getHasRulesFormComponent('rule'),
                        ]),
                    Tab::make('Presentation')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    static::getHasAffixesFormComponent('prefixLabel'),
                                    static::getHasAffixesFormComponent('suffixLabel'),
                                    TextInput::make('prefix'),
                                    TextInput::make('suffix'),
                                ]),
                            TextInput::make('separator')->helperText('You may allow the tags to be stored in a separated string, instead of JSON array. e.g. comma, space, etc.'),
                            TagsInput::make('suggestions'),
                            Checkbox::make('reorderable'),
                            TextInput::make('color')->datalist(['sanger', 'gray', 'info', 'primary', 'success', 'warning']),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Component $component): void
    {
        if (static::fiComponentHasTrait($component, HasAffixes::class)) {
            if ($this->prefixLabel) {
                $component->prefix($this->prefixLabel);
            }
            if ($this->suffixLabel) {
                $component->suffix($this->suffixLabel);
            }
        }
        if ($component instanceof TagsInput) {
            if ($this->prefix) {
                $component->tagPrefix($this->prefix);
            }
            if ($this->suffix) {
                $component->tagSuffix($this->suffix);
            }
            if ($this->color) {
                $component->color($this->color);
            }
            if ($this->separator) {
                $component->separator($this->separator);
            }
            if ($this->suggestions) {
                $component->suggestions($this->suggestions);
            }
            if ($this->reorderable) {
                $component->reorderable();
            }
        }
    }
}
