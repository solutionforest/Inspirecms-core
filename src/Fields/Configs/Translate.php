<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Concerns\HasInnerField;
use SolutionForest\InspireCms\Filament\Forms\Components\Translate as TranslateComponent;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

#[ConfigName('translate', 'Translate', 'Translate', 'heroicon-m-language')]
#[FormComponent(TranslateComponent::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
class Translate extends FieldTypeBaseConfig implements FieldTypeConfig
{
    use HasInnerField;

    public ?string $field = null;

    public array $fieldConfig = [];

    protected array $fieldVariable = [];

    public function getFormSchema(): array
    {
        $exceptsInnerFields = [
            'translate',
        ];
        return [

            Forms\Components\Section::make()
                ->heading('Field Configuration')
                ->compact()
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
                    Forms\Components\Group::make()
                        ->key('fieldConfig')
                        ->statePath('fieldConfig')
                        ->schema(function (Forms\Get $get) {

                            if ($field = $get('field')) {
                                return FilamentFieldGroup::getFieldTypeConfigFormSchema($field);
                            }

                            return [];
                        }),
                ]),
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

            $groupName = isset($this->fieldVariable['group']) ? $this->fieldVariable['group'] : (explode('.', $this->fieldVariable['statePath'] ?? null)[0] ?? null);

            $component
                ->schema([$fiFormComponent])
                // also set the state path for this component
                ->groupName($groupName);
        }
    }
}
