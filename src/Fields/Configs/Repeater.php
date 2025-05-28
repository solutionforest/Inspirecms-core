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
use SolutionForest\InspireCms\Fields\Configs\Concerns\HasColumnsLayoutConfig;
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
    use HasColumnsLayoutConfig;
    use HasInnerField;

    public array $fields = [];

    public ?string $itemLabel = null;

    public ?int $defaultItems = null;

    public bool $cloneable = false;

    public bool $collapsible = false;

    public bool $defaultCollapsed = false;

    public bool $reorderable = false;

    public bool $reorderableWithButtons = false;

    public bool $reorderableWithButtonsreorderableWithDragAndDrop = false;

    public ?int $minItems = null;

    public ?int $maxItems = null;

    public array $gridLayout = [];

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make('tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Presentation')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('cloneable'),
                                ]),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('collapsible'),
                                    Forms\Components\Toggle::make('defaultCollapsed'),
                                ]),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Toggle::make('reorderable')->default(true),
                                    Forms\Components\Toggle::make('reorderableWithButtons')->default(true),
                                    Forms\Components\Toggle::make('reorderableWithDragAndDrop')->default(false),
                                ]),

                            Forms\Components\TextInput::make('itemLabel')
                                ->inlineLabel()
                                ->placeholder('e.g. title, key, etc.')
                                ->helperText(str('The label for each item in the repeater. Using **`Name`** in the **Fields**')->markdown()->toHtmlString()),

                            Forms\Components\KeyValue::make('gridLayout')
                                ->keyLabel('Column')
                                ->keyLabel('Width')
                                ->keyPlaceholder('e.g. default, sm, md, lg, xl')
                                ->valuePlaceholder('e.g. 1, 2, 3, 4, etc.')
                                ->helperText(str('The grid layout for the repeater. Use **`default`** for the default layout, **`sm`** for small screens, **`md`** for medium screens, etc.')->markdown()->toHtmlString()),

                            static::getHasColumnsLayoutConfigComponent(),

                        ]),
                    Forms\Components\Tabs\Tab::make('Fields')
                        ->schema([
                            Forms\Components\TextInput::make('minItems')
                                ->inlineLabel()
                                ->integer(),
                            Forms\Components\TextInput::make('maxItems')
                                ->inlineLabel()
                                ->integer(),
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

            //
            $component->cloneable($this->cloneable);

            $component->collapsible($this->collapsible);
            $component->collapsed($this->defaultCollapsed ?? false);

            $component->reorderable($this->reorderable ?? false);
            $component->reorderableWithButtons($this->reorderableWithButtons ?? false);
            $component->reorderableWithDragAndDrop($this->reorderableWithDragAndDrops ?? false);

            //
            $component->itemLabel(function ($state) {
                if (is_array($state) && filled($this->itemLabel)) {
                    return $state[$this->itemLabel] ?? null;
                }

                return null;
            });

            if ($this->defaultItems != null) {
                $component->defaultItems($this->defaultItems);
            }

            if ($this->minItems != null) {
                $component->minItems($this->minItems);
            }
            if ($this->maxItems != null) {
                $component->maxItems($this->maxItems);
            }

            if (is_array($this->gridLayout) && ($filterGrid = $this->filterColumnsData($this->gridLayout)) && ! empty($filterGrid)) {
                $component->grid($filterGrid);
            }
            if (is_array($this->columnsLayout) && ($filterColumns = $this->filterColumnsData($this->columnsLayout)) && ! empty($filterColumns)) {
                $component->columns($filterColumns);
            }
        }
    }

    private function filterColumnsData(array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $key) => is_numeric($value) && $value > 0 && in_array($key, ['default', 'sm', 'md', 'lg', 'xl']))
            ->toArray();
    }
}
