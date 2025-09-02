<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Validation\ValidationException;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldInstructionsInput;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldLabelInput;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldMandatoryToggle;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldNameInput;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldStatePathInput;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\Components\FieldTypeInput;
use SolutionForest\InspireCms\Filament\Resources\Fields\Schemas\FieldForm;

class FieldGroupFieldsRepeater
{
    public static function make(): Repeater
    {
        return Repeater::make('fields')
            ->validationAttribute(__('inspirecms::resources/field-group.fields.validation_attribute'))
            ->key('fieldsRepeater')
            ->hiddenLabel()
            ->defaultItems(0)
            ->relationship('fields')
            ->itemLabel(fn (array $state): ?string => $state['label'] ?? $state['name'] ?? null)
            ->collapsible()->collapsed()
            ->orderColumn('sort')
            ->reorderableWithButtons()->reorderableWithDragAndDrop(false)
            ->addAction(
                fn (Action $action) => static::configureFieldsCreateActionOnRepeater($action)
            )
            ->extraItemActions([
                static::configureFieldsEditActionOnRepeater(
                    Action::make('edit')
                        ->icon(FilamentIcon::resolve('inspirecms::edit'))
                        ->label(__('filament-actions::edit.single.label'))
                ),
            ])
            ->schema(static::getFieldsRepeaterSchema());
    }

    protected static function getFieldsRepeaterSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    Hidden::make('id'),
                    Hidden::make('group_id'),
                    Hidden::make('sort'),
                    FieldTypeInput::make()
                        ->helperText('')
                        ->disabled()->saveRelationshipsWhenDisabled()->dehydrated()
                        ->columnSpanFull(),
                    Section::make()
                        ->heading(__('inspirecms::resources/field-group.sections.details.heading'))
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'field-group-field-details-section'])
                        ->aside()
                        ->schema([
                            FieldLabelInput::make()
                                ->helperText('')
                                ->disabled()->saveRelationshipsWhenDisabled()->dehydrated(),
                            FieldNameInput::make()
                                ->helperText('')
                                ->disabled()->saveRelationshipsWhenDisabled()->dehydrated(),
                            FieldStatePathInput::make()
                                ->helperText('')
                                ->hidden()->saveRelationshipsWhenHidden()->dehydrated(),

                            IconEntry::make('display_translatable')
                                ->label(__('inspirecms::resources/field.translatable.label'))
                                ->inlineLabel()
                                ->extraAttributes(['class' => 'flex justify-center'])
                                ->boolean()
                                ->state(fn ($get) => $get('config.translatable') ?? false)
                                ->falseColor('gray'),

                            IconEntry::make('display_mandatory')
                                ->label(__('inspirecms::resources/field.mandatory.label'))
                                ->inlineLabel()
                                ->extraAttributes(['class' => 'flex justify-center'])
                                ->boolean()
                                ->state(fn ($get) => $get('mandatory') ?? false)
                                ->falseColor('gray'),
                        ]),
                    FieldInstructionsInput::make()
                        ->helperText('')
                        ->disabled()->saveRelationshipsWhenDisabled()->dehydrated()
                        ->columnSpanFull(),
                    FieldMandatoryToggle::make()
                        ->hidden()
                        ->saveRelationshipsWhenHidden()->dehydratedWhenHidden(),

                    Hidden::make('config'),
                ]),
        ];
    }

    protected static function configureFieldsCreateActionOnRepeater(Action $action): Action
    {
        $modelName = strtolower(__('inspirecms::resources/field-group.fields.singular'));

        return $action
            ->size(Size::ExtraLarge)
            ->extraAttributes(['class' => 'w-full'])
            ->icon(FilamentIcon::resolve('inspirecms::add'))
            ->slideOver()
            ->modalWidth('5xl')
            ->fillForm(fn ($record) => [
                'group_id' => $record?->getKey(),
            ])
            ->schema(fn (Schema $schema) => FieldForm::configure($schema))
            ->label(fn () => __('inspirecms::buttons.add_with_name.label', [
                'name' => $modelName,
            ]))
            ->modalHeading(fn () => __('inspirecms::buttons.add_with_name.heading', [
                'name' => $modelName,
            ]))
            ->modalSubmitActionLabel(__('inspirecms::buttons.add.label'))
            ->before(function (array $data, Repeater $component) {
                // Add Uniqiue name validation
                $exisingState = collect($component->getState());
                // Get the existing names from the repeater state (can get form data from the action aftger original form validation)
                $nameToCheck = $data['name'] ?? null;

                // If the name is not empty and already exists in the existing names
                // throw a validation exception
                if ($nameToCheck && $exisingState->contains('name', $nameToCheck)) {
                    $validationAttribute = __('inspirecms::resources/field.name.validation_attribute');

                    throw ValidationException::withMessages([
                        'mountedFormComponentActionsData.0.name' => __('validation.distinct', ['attribute' => $validationAttribute]),
                    ]);
                }
            })
            ->action(function (array $data, Repeater $component) {
                $newUuid = $component->generateUuid();

                $items = $component->getState();

                if ($newUuid) {
                    $items[$newUuid] = $data;
                } else {
                    $items[] = $data;
                }

                $component->state($items);

                $component->getChildSchema($newUuid ?? array_key_last($items))->fill($data);

                $component->collapsed(false, shouldMakeComponentCollapsible: false);

                $component->callAfterStateUpdated();
            });
    }

    protected static function configureFieldsEditActionOnRepeater(Action $action): Action
    {
        return $action
            ->color('gray')
            ->slideOver()
            ->modalWidth('5xl')
            ->visible(function (Repeater $component) {
                if ($component->isDisabled()) {
                    return false;
                }

                return true;
            })
            ->fillForm(function (array $arguments, Repeater $component) {

                $itemData = $component->getRawItemState($arguments['item']);

                $relationship = $component->getRelationship();

                $existing = $component->getCachedExistingRecords()->get($arguments['item']);

                if ($existing) {
                    $model = $existing;
                } else {
                    $model = $relationship->getRelated()->fill($itemData);
                }

                return $model->attributesToArray();

            })
            ->schema(function (Schema $schema, array $arguments, Repeater $component) {
                return FieldForm::configure($schema)
                    ->model($component->getRelationship()->getRelated());
            })
            ->action(function (array $data, array $arguments, Repeater $component) {
                $uuid = $arguments['item'] ?? null;

                $items = $component->getState();

                if (filled($uuid) && isset($items[$uuid])) {
                    $items[$uuid] = $data;

                    $component->state($items);

                    $component->getChildSchema($uuid)->fill($data);

                    $component->collapsed(false, shouldMakeComponentCollapsible: false);

                    $component->callAfterStateUpdated();
                }
            });
    }
}
