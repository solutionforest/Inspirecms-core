<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Base\HasTemplates;
use SolutionForest\InspireCms\Models\Contracts\Template;

interface TemplateManagerInterface
{
    public function getCurrentTheme(): string;

    public function getAvailableThemes(): array;

    public function getComponentPrefix(): string;

    public function getComponentWithTheme(string $component, ?string $theme = null): string;

    /**
     * Assigns a default template to the given templateable object if it is not already set.
     *
     * @param  HasTemplates & Model  $templateable  The object that can have a template assigned to it.
     * @param  string |int | (Model & Template)  $template  The default template to assign if none is set.
     * @return void
     */
    public function assignDefaultTemplateIfNotSet($templateable, $template);

    /**
     * Retrieve the default content for the template.
     *
     * This method fetches and returns the default content that should be used
     * for the template. The content could be a string, an array, or any other
     * data structure depending on the implementation.
     *
     * @return string The default content for the template.
     */
    public function retrieveDefaultContent();

    /**
     * @param Model & Template $template
     * @return void
     */
    public function exportTemplate($template, ?string $theme = null): void;

    public function getExportedTemplateDir(): string;
}
