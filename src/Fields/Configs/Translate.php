<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Filament\Forms\Components\Translate as TranslateComponent;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

#[ConfigName('translate', 'Translate', 'Translate', 'heroicon-m-language')]
#[FormComponent(TranslateComponent::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
class Translate extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public ?string $field = null;

    public array $fieldConfig = [];

    protected array $fieldVariable = [];

    public function getFormSchema(): array
    {
        return [
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
            Forms\Components\Group::make()
                ->key('fieldConfig')
                ->statePath('fieldConfig')
                ->schema(function (Forms\Get $get) {

                    if ($field = $get('field')) {
                        return FilamentFieldGroup::getFieldTypeConfigFormSchema($field);
                    }

                    return [];
                }),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
    {
        if ($component instanceof TranslateComponent) {

            $fiFormComponent = FieldTypeHelper::performFormFieldFromConfig(
                $this->field,
                function ($fiFormConfig, $fiFormComponentFQCN) {

                    if (! isset($this->fieldVariable['name']) || blank($this->fieldVariable['name'])) {
                        throw new \Exception('The field variable name is required.');
                    }

                    return $fiFormComponentFQCN::make($this->fieldVariable['name'])
                        ->label($this->fieldVariable['label'])
                        ->helperText($this->fieldVariable['helperText'])
                        ->required($this->fieldVariable['required']);

                },
                $this->fieldConfig
            );

            $groupName = isset($this->fieldVariable['group']) ? $this->fieldVariable['group'] : (explode('.', $this->fieldVariable['statePath'])[0] ?? null);

            if (blank($groupName)) {
                throw new \Exception('The field group name is required.');
            }

            $component
                ->schema([$fiFormComponent])
                // also set the state path for this component
                ->groupName($groupName);
        }
    }

    public function setFieldVariable(array $variable): static
    {
        $this->fieldVariable = $variable;

        return $this;
    }
}
