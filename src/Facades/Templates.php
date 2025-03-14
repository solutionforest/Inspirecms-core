<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\TemplateManagerInterface;

/**
 * @method static string|null getCurrentTheme()
 * @method static void clearCurrentThemeCache()
 * @method static void resetCurrentTheme()
 * 
 * @method static array getAvailableThemes()
 * @method static bool isThemeExists(string $theme)
 * @method static string getComponentWithTheme(string $component, ?string $theme = null)
 * @method static string getComponentPathWithTheme(?string $componentName = null, ?string $theme = null)
 * 
 * @method static bool cloneTheme(string $sourceTheme, string $newTheme)
 * 
 * @method static void assignDefaultTemplateIfNotSet(\SolutionForest\InspireCms\Models\Contracts\Base\HasTemplates & \Illuminate\Database\Eloquent\Model $templateable, $template)
 * 
 * @method static void exportTemplate(\Illuminate\Database\Eloquent\Model & \SolutionForest\InspireCms\Models\Contracts\Template $template, ?string $theme = null)
 * @method static void ensureThemeLayoutComponentExists(string $theme, ?string $component = null)
 *
 * @see \SolutionForest\InspireCms\Base\TemplateManager
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
