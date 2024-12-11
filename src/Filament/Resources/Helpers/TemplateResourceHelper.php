<?php

namespace SolutionForest\InspireCms\Filament\Resources\Helpers;

use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Riodwanto\FilamentAceEditor\AceEditor;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Base\HasTemplates;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplateResourceHelper
{
    /**
     * @param  string  $name
     * @return Forms\Components\Field|Forms\Components\Component
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
            ->view('inspirecms::filament.forms.components.property-type-instructions');
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    public static function getPageComponentInstructionsFormComponent()
    {
        return Forms\Components\ViewField::make('page_component_instructions')
            ->label(__('inspirecms::resources/template.page_component_instructions.label'))
            ->view('inspirecms::filament.forms.components.page-component-instructions', [
                'copiedMessage' => __('inspirecms::actions.copy.message'),
                'copyButtonLabel' => __('inspirecms::actions.copy.label'),
            ]);
    }

    /**
     * Updates the view content at the specified path with the given content.
     *
     * @param  string  $fullPath  The full path to the view component file.
     * @param  string  $content  The new content to be written to the view component file.
     * @return void
     */
    public static function updateViewContentByPath($fullPath, $content)
    {
        file_put_contents($fullPath, $content);
    }

    /**
     * Retrieves the view content for the given record.
     *
     * @param  Template&Model  $record  The record for which to get the view component.
     * @return string The view component associated with the given record.
     */
    public static function getViewContent($record)
    {
        return file_get_contents($record->getFileFullPath());
    }

    /**
     * Updates the view content of a given record.
     *
     * @param  Template&Model  $record  The record to update.
     * @param  string  $content  The new content to set for the record.
     * @return void
     */
    public static function updateViewContent($record, $content)
    {
        static::updateViewContentByPath($record->getFileFullPath(), $content);
    }

    /**
     * @param  HasTemplates&Model  $templateable
     * @param  string|int|Model&Template  $template
     * @return void
     */
    public static function setDefaultTemplateIfEmpty($templateable, $template)
    {
        if (is_null(value: $templateable->getDefaultTemplate())) {
            $templateable->setAsDefaultTemplate($template);
        }
    }
}
