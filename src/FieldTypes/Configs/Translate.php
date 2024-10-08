<?php

namespace SolutionForest\InspireCms\FieldTypes\Configs;

use Filament\Forms;
use Pboivin\FilamentPeek\Livewire\BuilderEditor;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

#[ConfigName('translate', 'Translate', 'Translate', 'heroicon-m-language')]
#[FormComponent(Forms\Components\Group::class)]
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
        $langs = InspireCms::getAllAvailableLanguages();

        $component->schema(function ($livewire) use ($langs) {

            $components = [];
            foreach ($langs as $lang) {

                $langCode = $lang->getCode();
                $fiFormComponent = FieldTypeHelper::performFormFieldFromConfig($this->field, function ($fiFormConfig, $fiFormComponentFQCN) use ($langCode) {

                    if (! isset($this->fieldVariable['name']) || blank($this->fieldVariable['name'])) {
                        throw new \Exception('The field variable name is required.');
                    }

                    $fiFormComponent = $fiFormComponentFQCN::make($this->fieldVariable['name']);

                    $fiFormComponent->label($this->fieldVariable['label']);
                    $fiFormComponent->helperText($this->fieldVariable['helperText']);
                    $fiFormComponent->required($this->fieldVariable['required']);

                    $fiFormComponent->statePath($this->fieldVariable['name'] . '.' . $langCode);

                    return $fiFormComponent;

                }, $this->fieldConfig);

                if (! $fiFormComponent) {
                    continue;
                }

                if ($livewire instanceof ContentForm) {
                    $livewire->setPropertyDataTranslationFields([$this->fieldVariable['name']], true);

                    $fiFormComponent
                        ->hidden($langCode != $livewire->getActiveActionsLocale())
                        ->dehydratedWhenHidden();
                } elseif ($livewire instanceof BuilderEditor) {
                    $activeLocale = $livewire->editorData['activeLocale'] ?? null;
                    $fiFormComponent
                        ->hidden($langCode != $activeLocale)
                        ->dehydratedWhenHidden();
                }

                $fiFormComponent->translatable();

                $components[] = $fiFormComponent;
            }

            return $components;
        });
    }

    public function setFieldVariable(array $variable): static
    {
        $this->fieldVariable = $variable;

        return $this;
    }
}
