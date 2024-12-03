<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

#[ConfigName('repeater', 'Repeater', 'List', 'heroicon-o-queue-list')]
#[FormComponent(Forms\Components\Repeater::class)]
#[DbType('mysql', 'text')]
#[DbType('sqlite', 'text')]
class Repeater extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public array $fields = [];

    public function getFormSchema(): array
    {
        $exceptsInnerFields = [
            'repeater',
        ];

        return [
            Forms\Components\Repeater::make('fields')
                ->columnSpanFull()
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('field')
                                ->options(fn () => FieldTypeHelper::getFieldTypeOptions(excepts: $exceptsInnerFields))
                                ->getSearchResultsUsing(fn ($search) => FieldTypeHelper::getFieldTypeOptions($search, excepts: $exceptsInnerFields))
                                ->searchable()->allowHtml()
                                ->required()
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn (Forms\Components\Select $component) => $component
                                    ->getContainer()
                                    ->getComponent('fieldConfig')
                                    ?->getChildComponentContainer()
                                    ?->fill()),
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('label'),
                            Forms\Components\TextInput::make('helperText'),
                            Forms\Components\Toggle::make('isRequired')->default(false),
                        ]),
                        Forms\Components\Group::make()
                        ->key('fieldConfig')
                        ->statePath('fieldConfig')
                        ->schema(fn (Forms\Get $get)  => FieldTypeHelper::getFieldConfigFormSchemaForFieldType($get('field')))
                ])
                ->defaultItems(1),
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
        }
    }
}
