<?php

namespace SolutionForest\InspireCms\Filament\Resources\Helpers;

use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

/**
 * Class FieldHelper
 *
 * This class provides helper methods for handling form fields in the Filament package for model @see \SolutionForest\InspireCms\Models\Contracts\Field.
 */
class FieldResourceHelper
{
    public static function getEditFormSchema(): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Hidden::make('id'),
                    Forms\Components\Hidden::make('group_id'),
                    Forms\Components\Hidden::make('sort'),
                    static::getStatePathFormComponent()->hidden(),
                    static::getLabelFormComponent(),
                    static::getNameFormComponent(),
                    static::getInstructionsFormComponent(),
                    static::getTypeFormComponent(),
                ]),

            Forms\Components\Section::make()
                ->schema([
                    static::getMandatoryFormComponent()->columnSpanFull(),
                ]),
            static::getConfigFormComponent(),
        ];
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::forms/fields/field.name.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::forms/fields/field.name.label'))
            ->helperText(__('inspirecms::forms/fields/field.name.helper'))
            ->required()
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state, '_')))
            ->unique(table: InspireCmsConfig::getFieldModelClass(), column: 'name', ignorable: function ($component, Forms\Get $get) {
                $id = $get('id');

                return InspireCmsConfig::getFieldModelClass()::find($id);
            }, modifyRuleUsing: function (Unique $rule, ?Model $record, $get) {
                $groupId = $record?->group_id ?? $get('group_id') ?? 0;

                return $rule->where('group_id', $groupId);
            });
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getLabelFormComponent()
    {
        return Forms\Components\TextInput::make('label')
            ->label(__('inspirecms::forms/fields/field.label.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::forms/fields/field.label.label'))
            ->helperText(__('inspirecms::forms/fields/field.label.helper'))
            ->required()
            ->columnSpan('full')
            ->maxLength(255)
            ->live(debounce: 500)
            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('name', Str::slug($state, '_')));
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getInstructionsFormComponent()
    {
        return Forms\Components\TextInput::make('instructions')
            ->label(__('inspirecms::forms/fields/field.instructions.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::forms/fields/field.instructions.label'))
            ->helperText(__('inspirecms::forms/fields/field.instructions.helper'))
            ->maxLength(255)
            ->columnSpan('full');
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getTypeFormComponent()
    {
        return Forms\Components\Select::make('type')
            ->label(__('inspirecms::forms/fields/field.type.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::forms/fields/field.type.label'))
            ->helperText(__('inspirecms::forms/fields/field.type.helper'))
            ->columns(4)
            ->searchable()
            ->options(fn () => FieldTypeHelper::getFieldTypeOptions())
            ->getSearchResultsUsing(fn ($search) => FieldTypeHelper::getFieldTypeOptions($search))
            ->preload()
            ->allowHtml()
            ->required()
            ->columnSpan('full')
            ->live(debounce: 500)
            ->afterStateUpdated(fn (Forms\Components\Select $component) => $component
                ->getContainer()
                ->getParentComponent()->getContainer() // section
                ->getComponent('configFields')
                ?->getChildComponentContainer()
                ?->fill());
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getStatePathFormComponent()
    {
        return Forms\Components\TextInput::make('state_path')
            ->label(__('inspirecms::forms/fields/field.state_path.label'))
            ->inlineLabel()
            ->placeholder(__('inspirecms::forms/fields/field.state_path.label'))
            ->helperText(__('inspirecms::forms/fields/field.state_path.helper'))
            ->maxLength(255);
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getMandatoryFormComponent()
    {
        return Forms\Components\Toggle::make('mandatory')
            ->label(__('inspirecms::forms/fields/field.mandatory.label'))
            ->inlineLabel()
            ->helperText(__('inspirecms::forms/fields/field.mandatory.helper'))
            ->inlineLabel();
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getConfigFormComponent($key = 'configFields', $statePath = 'config')
    {
        return Forms\Components\Group::make()
            ->key($key)
            ->statePath($statePath)
            ->schema(fn (Forms\Get $get) => FieldTypeHelper::getFieldConfigFormSchemaForFieldType($get('type')));
    }
}
