<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
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
        return [
            Forms\Components\Repeater::make('fields')
                ->columnSpanFull()
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                ->schema([
                    Forms\Components\Select::make('field')
                        ->options(function () {
                            $options = FilamentFieldGroup::getFieldTypeGroupedKeyValueWithIconOptions();
                            // filter out the current field type
                            $configNames = $this->getConfigNames()[0];
                            unset($options[$configNames['group']]);
        
                            return $options;
                        })
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
                    Forms\Components\Group::make()
                        ->key('fieldConfig')
                        ->statePath('fieldConfig')
                        ->schema(function (Forms\Get $get) {
        
                            if ($field = $get('field')) {
                                return FilamentFieldGroup::getFieldTypeConfigFormSchema($field);
                            }
        
                            return [];
                        }),
                ])
                ->defaultItems(1),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
    {
        if ($component instanceof Forms\Components\Repeater) {
            
            $components = [];

            // $groupName = $component->getName();

            foreach ($this->fields as $index => $data) {
                if (!isset($data['field']) || blank($data['field'])) {
                    throw new \Exception('The field type is required.');
                }
                if (!isset($data['name']) || blank($data['name'])) {
                    throw new \Exception('The field name is required.');
                }

                $components[] = FieldTypeHelper::performFormFieldFromConfig(
                    $data['field'],
                    function ($fiFormConfig, $fiFormComponentFQCN) use ($data, $index) {

                        $fieldName = $data['name'];
                        $label = $data['label'] ?? null;
                        $helperText = $data['helperText'] ?? null;
                        $mandatory = $data['isRequired'] ?? false;

                        if (is_subclass_of($fiFormComponentFQCN, \Filament\Forms\Components\Field::class)) {
                            $fiFormComponent = $fiFormComponentFQCN::make($fieldName);
        
                            $fiFormComponent->label($label);
                            $fiFormComponent->helperText($helperText);
                            $fiFormComponent->required($mandatory);
                            
        
                        } else {
        
                            $fiFormComponent = null;
                        }
        
                        if (in_array(\SolutionForest\InspireCms\Fields\Configs\Concerns\HasInnerField::class, class_uses($fiFormConfig))) {
        
                            $fiFormConfig->setFieldVariable([
                                'name' => $fieldName,
                                'label' => $label,
                                'helperText' => $helperText,
                                'required' => $mandatory,
                            ]);
        
                            $fiFormComponent = $fiFormComponentFQCN::make();
                        }
        
                        return $fiFormComponent;
                    },
                    $data['fieldConfig'] ?? []
                );
            }
            $component->schema(array_filter($components));
        }
    }
}
