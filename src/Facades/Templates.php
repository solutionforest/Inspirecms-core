<?php

namespace SolutionForest\InspireCms\Facades;

use Illuminate\Support\Facades\Facade;
use SolutionForest\InspireCms\Base\TemplateManagerInterface;

/**
 * @method static string|null getCurrentTheme()
 * @method static array getAvailableThemes()
 * @method static bool isThemeExists(string $theme)
 * @method static void clearCurrentThemeCache()
 * @method static void resetCurrentTheme()
 * @method static string getComponentPrefix()
 * @method static string getComponentWithTheme(string $component, ?string $theme = null)
 * @method static string getComponentDirectoryForTheme(?string $theme = null)
 * @method static void assignDefaultTemplateIfNotSet(\SolutionForest\InspireCms\Models\Contracts\Base\HasTemplates & \Illuminate\Database\Eloquent\Model $templateable, $template)
 * @method static string retrieveDefaultContent()
 * @method static void exportTemplate(\Illuminate\Database\Eloquent\Model & \SolutionForest\InspireCms\Models\Contracts\Template $template, ?string $theme = null)
 * @method static string getExportedTemplateDir()
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
