<?php

namespace SolutionForest\InspireCms\Filament\Resources\Helpers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\InspireCmsConfig;

class FieldGroupResourceHelper
{
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    public static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::forms/fields/field-group.title.label'))
            ->required()
            ->maxLength(255)
            ->live(true, 500)
            ->afterStateUpdated(function ($operation, $state, Forms\Get $get, Forms\Set $set) {
                // Fill slug if empty / operation is create
                if ($operation === 'create' || empty($get('name'))) {
                    $set('name', Str::slug($state, '_'));
                }
            })
            ->autofocus();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    public static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::forms/fields/field-group.name.label'))
            ->required()
            ->maxLength(255)
            ->live(true, 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state, '_')))
            ->unique(
                table: InspireCmsConfig::getFieldGroupModelClass(),
                column: 'name',
                ignoreRecord: true
            );
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    public static function getActiveFormComponent()
    {
        return Forms\Components\Hidden::make('active')
            ->dehydratedWhenHidden(true)
            ->dehydrateStateUsing(fn () => true);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    public static function getFieldsFormComponent()
    {
        return Forms\Components\Section::make()
            ->heading(fn () => __('inspirecms::forms/fields/field-group.fields.label'))
            ->aside()
            ->compact()
            ->schema([
                FieldGroupResourceHelper::getFieldsRepeater(),
            ]);
    }

    public static function getFieldsRepeater()
    {
        return Forms\Components\Repeater::make('fields')
            ->key('fieldsRepeater')
            ->hiddenLabel()
            ->defaultItems(0)
            ->relationship('fields')
            ->itemLabel(fn (array $state): ?string => $state['label'] ?? $state['name'] ?? null)
            ->collapsible()->collapsed()
            ->orderColumn('sort')
            ->reorderableWithButtons()->reorderableWithDragAndDrop(false)
            ->addActionLabel(fn () => __('inspirecms::inspirecms.add_xxx', ['name' => strtolower(__('inspirecms::inspirecms.fields'))]))
            ->addAction(
                fn (Forms\Components\Actions\Action $action) => static::configureFieldsCreateActionOnRepeater($action)
            )
            ->extraItemActions([
                static::configureFieldsEditActionOnRepeater(
                    Forms\Components\Actions\Action::make('edit')
                        ->icon(FilamentIcon::resolve('actions::edit-action') ?? 'heroicon-m-pencil-square')
                        ->label(__('filament-actions::edit.single.label'))
                ),
            ])
            ->schema(static::getFieldsRepeaterSchema());
    }

    protected static function getFieldsRepeaterSchema(): array
    {
        return [
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Hidden::make('id'),
                    Forms\Components\Hidden::make('group_id'),
                    Forms\Components\Hidden::make('sort'),
                    FieldResourceHelper::getTypeFormComponent()->helperText('')
                        ->disabled()->saveRelationshipsWhenDisabled()->dehydrated()
                        ->columnSpanFull(),
                    Forms\Components\Section::make(__('inspirecms::inspirecms.details'))
                        ->columnSpanFull()
                        ->aside()
                        ->schema([
                            FieldResourceHelper::getLabelFormComponent()->helperText('')
                                ->disabled()->saveRelationshipsWhenDisabled()->dehydrated(),
                            FieldResourceHelper::getNameFormComponent()->helperText('')
                                ->disabled()->saveRelationshipsWhenDisabled()->dehydrated(),
                            FieldResourceHelper::getStatePathFormComponent()->helperText('')
                                ->hidden()->saveRelationshipsWhenHidden()->dehydrated(),
                        ]),
                    FieldResourceHelper::getInstructionsFormComponent()->helperText('')
                        ->disabled()->saveRelationshipsWhenDisabled()->dehydrated()
                        ->columnSpanFull(),
                    FieldResourceHelper::getMandatoryFormComponent()->hidden()
                        ->saveRelationshipsWhenHidden()->dehydratedWhenHidden(),
                    Forms\Components\Hidden::make('config'),
                ]),
        ];
    }

    protected static function getFieldsEditFormSchema(): array
    {
        return FieldResourceHelper::getEditFormSchema();
    }

    protected static function configureFieldsCreateActionOnRepeater(Forms\Components\Actions\Action $action): Forms\Components\Actions\Action
    {
        return $action
            ->size(ActionSize::ExtraLarge)
            ->extraAttributes(['class' => 'w-full'])
            ->slideOver()
            ->modalWidth('5xl')
            ->fillForm(fn ($record) => [
                'group_id' => $record?->getKey(),
            ])
            ->form(static::getFieldsEditFormSchema())
            ->action(function (array $data, Forms\Components\Repeater $component) {
                $newUuid = $component->generateUuid();

                $items = $component->getState();

                if ($newUuid) {
                    $items[$newUuid] = $data;
                } else {
                    $items[] = $data;
                }

                $component->state($items);

                $component->getChildComponentContainer($newUuid ?? array_key_last($items))->fill($data);

                $component->collapsed(true, shouldMakeComponentCollapsible: true);

                $component->callAfterStateUpdated();
            });
    }

    protected static function configureFieldsEditActionOnRepeater(Forms\Components\Actions\Action $action): Forms\Components\Actions\Action
    {
        return $action
            ->color('gray')
            ->slideOver()
            ->modalWidth('5xl')
            ->visible(function (Forms\Components\Repeater $component) {
                if ($component->isDisabled()) {
                    return false;
                }

                return true;
            })
            ->fillForm(function (array $arguments, Forms\Components\Repeater $component) {

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
            ->form(function (Form $form, array $arguments, Forms\Components\Repeater $component) {
                return $form
                    ->model($component->getRelationship()->getRelated())
                    ->schema(static::getFieldsEditFormSchema());
            })
            ->action(function (array $data, array $arguments, Forms\Components\Repeater $component) {
                $uuid = $arguments['item'] ?? null;

                $items = $component->getState();

                if (filled($uuid) && isset($items[$uuid])) {
                    $items[$uuid] = $data;

                    $component->state($items);

                    $component->getChildComponentContainer($uuid)->fill($data);

                    $component->collapsed(false, shouldMakeComponentCollapsible: false);

                    $component->callAfterStateUpdated();
                }
            });
    }
}
