<?php

namespace SolutionForest\InspireCms\Filament\Resources\Helpers;

use Filament\Forms;
use Illuminate\Support\Str;
use Riodwanto\FilamentAceEditor\AceEditor;
use SolutionForest\InspireCms\InspireCmsConfig;

class TemplateResourceHelper
{
    public static function getThemeSelectOptions(): array
    {
        return collect(inspirecms_templates()->getAvailableThemes())
            ->mapWithKeys(function ($theme) {
                return [
                    $theme => $theme,
                ];
            })
            ->all();
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    public static function getThemeFormComponent()
    {
        return Forms\Components\Select::make('theme')
            ->label(__('inspirecms::resources/template.theme.label'))
            ->prefixIcon('heroicon-o-paint-brush')
            ->options(static::getThemeSelectOptions());
    }

    /**
     * @param  string  $name
     * @return Forms\Components\Field|Forms\Components\Component|AceEditor
     */
    public static function getContentFormComponent($name = 'content')
    {
        return AceEditor::make($name)
            ->label(__('inspirecms::resources/template.content.label'))
            ->mode('php')
            ->darkTheme('tomorrow_night_eighties')
            ->height('48rem');
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getSlugFormComponent()
    {
        return Forms\Components\TextInput::make('slug')
            ->label(__('inspirecms::resources/template.slug.label'))
            ->validationAttribute(__('inspirecms::resources/template.slug.validation_attribute'))
            ->inlineLabel()
            ->required()
            ->maxLength(255)
            ->live(true, 500)
            ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state)))
            ->unique(
                table: InspireCmsConfig::getTemplateModelClass(),
                column: 'slug',
                ignoreRecord: true
            );
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getPropertyTypeInstructionsFormComponent()
    {
        return Forms\Components\ViewField::make('property_type_instructions')
            ->label(__('inspirecms::resources/template.property_type_instructions.label'))
            ->view('inspirecms::instructions.property-type-instructions');
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getPageComponentInstructionsFormComponent()
    {
        return Forms\Components\ViewField::make('page_component_instructions')
            ->label(__('inspirecms::resources/template.page_component_instructions.label'))
            ->view('inspirecms::instructions.page-component-instructions', [
                'plaintext' => '<x-cms-template :content="$content" type="page">
    Your content here
</x-cms-template>'
            ]);
    }
}
