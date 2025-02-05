<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use Illuminate\Support\Str;
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

        $getItemKeyForRepeaterAction = fn (array $arguments): string => $arguments['item'];

        $getItemStateForRepeaterAction = fn (array $arguments, Forms\Components\Repeater $component): array => 
            $component->getRawItemState($getItemKeyForRepeaterAction($arguments));

        $getFieldForRepeaterAction = fn (array $arguments, Forms\Components\Repeater $component): ?string => 
            $getItemStateForRepeaterAction($arguments, $component)['field'] ?? null;

        $getFieldIconForRepeaterAction = function (array $arguments, Forms\Components\Repeater $component) use ($getFieldForRepeaterAction): ?string {
            if (($field = $getFieldForRepeaterAction($arguments, $component)) && ($icons = FieldTypeHelper::getFieldTypeIcon($field))) {
                return is_array($icons) ? $icons[0] : $icons;
            }
            return null;
        };

        return [
            Forms\Components\Repeater::make('fields')
                ->columnSpanFull()
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                ->addActionLabel('Add Field')
                ->schema(function ($state) use ($exceptsInnerFields) {
                    return [
                        Forms\Components\Hidden::make('fieldConfig')->dehydrated()->dehydrateStateUsing(fn ($state) => $state ?? []),
                        Forms\Components\Select::make('field')
                            ->options(fn () => FieldTypeHelper::getFieldTypeOptions(excepts: $exceptsInnerFields))
                            ->getSearchResultsUsing(fn ($search) => FieldTypeHelper::getFieldTypeOptions($search, excepts: $exceptsInnerFields))
                            ->searchable()->allowHtml()
                            ->required()
                            ->live()
                            // todo: add translation
                            ->hintIcon('heroicon-o-information-circle', 'Resetting the field type will clear the field configuration.')
                            ->afterStateUpdated(function ($old, $state, $set) {
                                if ($old !== $state) {
                                    $set('fieldConfig', []);
                                }
                            }),
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->helperText('Label for the field')
                            ->live()->afterStateUpdated(fn ($state, $set) => $state ? $set('name', Str::slug($state)) : null),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->helperText('Unique name for the field'),
                        Forms\Components\TextInput::make('helperText'),
                        Forms\Components\Toggle::make('isRequired')
                            ->label('Is Required?')
                            ->default(false),
                    ];
                })
                ->defaultItems(1)
                ->extraItemActions([
                    Forms\Components\Actions\Action::make('editConfig')
                        ->icon('heroicon-s-cog-8-tooth')
                        ->slideOver()
                        // todo: add translation
                        ->modalHeading(fn (array $arguments, Forms\Components\Repeater $component) => str_replace([':field'], [$getFieldForRepeaterAction($arguments, $component) ?? 'Field'], 'Edit :field configuration'))
                        ->modalIcon(fn (array $arguments, Forms\Components\Repeater $component) => $getFieldIconForRepeaterAction($arguments, $component))
                        ->disabled(fn (array $arguments, Forms\Components\Repeater $component) => empty($getFieldForRepeaterAction($arguments, $component)))
                        ->form(fn (Forms\Form $form, array $arguments, Forms\Components\Repeater $component) => $form
                            ->schema(FieldTypeHelper::getRepeaterFieldConfigSchemaForFieldType($getFieldForRepeaterAction($arguments, $component)))
                        )
                        ->fillForm(function (array $arguments, Forms\Components\Repeater $component) use ($getItemStateForRepeaterAction) {
                            $existingFieldConfig = $getItemStateForRepeaterAction($arguments, $component)['fieldConfig'];
                            if (! empty($existingFieldConfig)) {
                                return $existingFieldConfig;
                            }
                        })
                        ->action(function (array $data, array $arguments, Forms\Components\Repeater $component) use ($getItemKeyForRepeaterAction) {

                            $itemKey = $getItemKeyForRepeaterAction($arguments);

                            $itemState = $component->getRawItemState($itemKey);

                            $itemState['fieldConfig'] = $data;
                            
                            $component->getChildComponentContainer($itemKey)->fill($itemState);
            
                            $component->collapsed(false, shouldMakeComponentCollapsible: false);
            
                            $component->callAfterStateUpdated();
                        })
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
        }
    }
}
