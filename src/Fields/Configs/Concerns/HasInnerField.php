<?php

namespace SolutionForest\InspireCms\Fields\Configs\Concerns;

use Filament\Forms;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

trait HasInnerField
{
    protected static array $exceptsInnerFields = [
        'repeater',
    ];

    protected static function getHasInnerFieldComponent()
    {
        $getItemKeyForRepeaterAction = fn (array $arguments): string => $arguments['item'];

        $getItemStateForRepeaterAction = fn (array $arguments, Forms\Components\Repeater $component): array => $component->getRawItemState($getItemKeyForRepeaterAction($arguments));

        $getFieldForRepeaterAction = fn (array $arguments, Forms\Components\Repeater $component): ?string => $getItemStateForRepeaterAction($arguments, $component)['field'] ?? null;

        $getFieldIconForRepeaterAction = function (array $arguments, Forms\Components\Repeater $component) use ($getFieldForRepeaterAction): ?string {
            if (($field = $getFieldForRepeaterAction($arguments, $component)) && ($icons = FieldTypeHelper::getFieldTypeIcon($field))) {
                return is_array($icons) ? $icons[0] : $icons;
            }

            return null;
        };

        return Forms\Components\Repeater::make('fields')
                ->columnSpanFull()
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                ->addActionLabel('Add Field')
                ->schema(static::getHasInnerFieldFieldsSchema())
                ->defaultItems(1)
                ->extraItemActions([
                    Forms\Components\Actions\Action::make('editConfig')
                        ->icon('heroicon-s-cog-8-tooth')
                        ->slideOver()
                        // todo: add translation
                        ->modalHeading(fn (array $arguments, Forms\Components\Repeater $component) => str_replace([':field'], [$getFieldForRepeaterAction($arguments, $component) ?? 'Field'], 'Edit :field configuration'))
                        ->modalIcon(fn (array $arguments, Forms\Components\Repeater $component) => $getFieldIconForRepeaterAction($arguments, $component))
                        ->disabled(fn (array $arguments, Forms\Components\Repeater $component) => empty($getFieldForRepeaterAction($arguments, $component)))
                        ->form(function (Forms\Form $form, array $arguments, Forms\Components\Repeater $component) use ($getFieldForRepeaterAction) {
                            
                            $innerFieldTypeName = $getFieldForRepeaterAction($arguments, $component);
                            
                            if (filled($innerFieldTypeName) && 
                                ($fieldTypeConfig = FieldTypeHelper::getFieldTypeConfig($innerFieldTypeName)) 
                            ) {
                                if ($fieldTypeConfig->isFieldTypeTranslatable()) {
                                    // display "translatable" field for the field type
                                    return $fieldTypeConfig->getEnhancedFormSchema();
                                } else {
                                    return $fieldTypeConfig->getFormSchema();
                                }
                            }

                            return [];
                        })
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
                        }),
                ]);
    }

    protected static function getHasInnerFieldFieldsSchema(): array
    {
        return [
            Forms\Components\Hidden::make('fieldConfig')
                ->dehydrated()
                ->dehydrateStateUsing(fn ($state) => $state ?? []),

            Forms\Components\Select::make('field')
                ->options(fn () => FieldTypeHelper::getFieldTypeOptions(excepts: static::getExceptsInnerFields()))
                ->getSearchResultsUsing(fn ($search) => FieldTypeHelper::getFieldTypeOptions($search, excepts: static::getExceptsInnerFields()))
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
    }

    protected static function getExceptsInnerFields(): array
    {
        return static::$exceptsInnerFields;
    }
}
