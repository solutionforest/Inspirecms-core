<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Concerns;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;

#[ConfigName('tags', 'Tags', 'List', 'heroicon-o-tag')]
#[FormComponent(Forms\Components\TagsInput::class)]
#[DbType('mysql', 'text')]
#[DbType('sqlite', 'text')]
class Tags extends FieldTypeBaseConfig implements FieldTypeConfig
{
    use Concerns\HasAffixes;
    use Concerns\HasRules;

    public ?string $separator = null;

    public ?array $suggestions = null;

    public ?bool $reorderable = null;

    public ?string $color = null;

    public ?string $suffix = null;

    public ?string $prefix = null;

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make('tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Validation')
                        ->schema([
                            static::getHasRulesFormComponent('rule'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Presentation')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    static::getHasAffixesFormComponent('prefixLabel'),
                                    static::getHasAffixesFormComponent('suffixLabel'),
                                    Forms\Components\TextInput::make('prefix'),
                                    Forms\Components\TextInput::make('suffix'),
                                ]),
                            Forms\Components\TextInput::make('separator')->helperText('You may allow the tags to be stored in a separated string, instead of JSON array. e.g. comma, space, etc.'),
                            Forms\Components\TagsInput::make('suggestions'),
                            Forms\Components\Checkbox::make('reorderable'),
                            Forms\Components\TextInput::make('color')->datalist(['sanger', 'gray', 'info', 'primary', 'success', 'warning']),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
    {
        if (static::fiComponentHasTrait($component, \Filament\Forms\Components\Concerns\HasAffixes::class)) {
            if ($this->prefixLabel) {
                $component->prefix($this->prefixLabel);
            }
            if ($this->suffixLabel) {
                $component->suffix($this->suffixLabel);
            }
        }
        if ($component instanceof Forms\Components\TagsInput) {
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
