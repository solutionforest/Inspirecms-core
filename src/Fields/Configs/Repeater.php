<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Converter;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Translatable;
use SolutionForest\InspireCms\Fields\Configs\Concerns\HasInnerField;
use SolutionForest\InspireCms\Fields\Converters\RepeaterConverter;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

#[ConfigName('repeater', 'Repeater', 'List', 'heroicon-o-queue-list')]
#[FormComponent(Forms\Components\Repeater::class)]
#[DbType('mysql', 'text')]
#[DbType('sqlite', 'text')]
#[Converter(RepeaterConverter::class)]
#[Translatable(false)]
class Repeater extends FieldTypeBaseConfig implements FieldTypeConfig
{
    use HasInnerField;

    public array $fields = [];

    public bool $collapsible = false;

    public bool $cloneable = false;

    public bool $defaultCollapsed = false;

    public ?string $itemLabel = null;

    public ?int $defaultItems = null;

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make('tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Presentation')
                        ->schema([
                            Forms\Components\Toggle::make('collapsible'),
                            Forms\Components\Toggle::make('cloneable'),
                            Forms\Components\Toggle::make('defaultCollapsed'),
                            Forms\Components\TextInput::make('itemLabel')
                                ->inlineLabel()
                                ->placeholder('e.g. title, key, etc.')
                                ->helperText(str('The label for each item in the repeater. Using **`Name`** in the **Fields**')->markdown()->toHtmlString()),
                            ]),
                    Forms\Components\Tabs\Tab::make('Fields')
                        ->schema([
                            Forms\Components\TextInput::make('defaultItems')
                                ->inlineLabel()
                                ->placeholder('e.g. 1, 2, 3, etc.')
                                ->integer()
                                ->minValue(0)
                                ->default(1)
                                ->helperText(str('The default number of items to show in the repeater.')->markdown()->toHtmlString()),
                            static::getHasInnerFieldComponent()->hiddenLabel(),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
    {
        if ($component instanceof Forms\Components\Repeater) {

            $components = [];

            foreach ($this->fields as $index => $data) {

                if (! isset($data['field']) || blank($data['field'])) {
                    throw new \Exception('The field type is required.');
                }
                if (! isset($data['name']) || blank($data['name'])) {
                    throw new \Exception('The field name is required.');
                }

                $components[] = FieldTypeHelper::buildFieldForFieldType(
                    fieldTypeName: $data['field'],
                    fieldTypeConfig: $data['fieldConfig'] ?? [],
                    name: $data['name'],
                    label: $data['label'] ?? null,
                    helperText: $data['helperText'] ?? null,
                    required: $data['isRequired'] ?? false,
                    groupName: null,
                );
            }
            $component->schema(array_filter($components));

            $component->collapsible($this->collapsible);

            $component->cloneable($this->cloneable);

            $component->collapsed($this->defaultCollapsed ?? false);
            
            $component->itemLabel(function ($state) {
                if (is_array($state) && filled($this->itemLabel)) {
                    return $state[$this->itemLabel] ?? null;
                }

                return null;
            });

            if ($this->defaultItems != null) {
                $component->defaultItems($this->defaultItems);
            }
        }
    }
}
