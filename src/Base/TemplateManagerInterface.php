<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Base\HasTemplates;
use SolutionForest\InspireCms\Models\Contracts\Template;

interface TemplateManagerInterface
{
    /**
     * Get the current theme.
     *
     * @return string|null The current theme name, or null if no theme is set.
     */
    public function getCurrentTheme(): ?string;

    public function clearCurrentThemeCache(): void;

    public function resetCurrentTheme(): void;

    public function getAvailableThemes(): array;

    public function isThemeExists(string $theme): bool;

    public function getComponentWithTheme(string $componentName, ?string $theme = null): string;

    public function getComponentPathWithTheme(?string $componentName = null, ?string $theme = null): string;

    public function getThemeDefaultLayoutPath(?string $theme = null): string;

    public function cloneTheme(string $sourceTheme, string $newTheme): bool;

    /**
     * Assigns a default template to the given templateable object if it is not already set.
     *
     * @param  HasTemplates & Model  $templateable  The object that can have a template assigned to it.
     * @param  string |int | (Model & Template)  $template  The default template to assign if none is set.
     * @return void
     */
    public function assignDefaultTemplateIfNotSet($templateable, $template);

    /**
     * @param  Model & Template  $template
     */
    public function exportTemplate($template, ?string $theme = null): void;
}
