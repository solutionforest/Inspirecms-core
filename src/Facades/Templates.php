<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\TemplateManager;
use SolutionForest\InspireCms\Base\TemplateManagerInterface;
use SolutionForest\InspireCms\Models\Contracts\Base\HasTemplates;
use SolutionForest\InspireCms\Models\Contracts\Template;

/**
 * @method static string|null getCurrentTheme()
 * @method static void clearCurrentThemeCache()
 * @method static void resetCurrentTheme()
 * @method static array getAvailableThemes()
 * @method static string getPath() Get the path to the theme directory.
 * @method static bool isThemeExists(string $theme)
 * @method static bool hasComponent(string $componentName, ?string $theme = null)
 * @method static string getComponentWithTheme(string $component, ?string $theme = null)
 * @method static string getComponentPathWithTheme(?string $componentName = null, ?string $theme = null)
 * @method static bool cloneTheme(string $sourceTheme, string $newTheme)
 * @method static void assignDefaultTemplateIfNotSet(HasTemplates&Model $templateable, $template)
 * @method static void exportTemplate(Model&Template $template, ?string $theme = null)
 *
 * @see TemplateManager
 */
class Templates extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return TemplateManagerInterface::class;
    }
}
