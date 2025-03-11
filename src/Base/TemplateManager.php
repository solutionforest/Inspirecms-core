<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplateManager implements TemplateManagerInterface
{
    protected CacheManager $cacheManager;

    protected string $theme;

    protected array $themes = [];

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;

        $this->theme = trim(InspireCmsConfig::get('template.theme', 'manifest'));
        $this->themes = collect(InspireCmsConfig::get('template.themes', []))
            // sort the default theme to the top
            ->sortBy(fn ($value, $key) => $key === $this->theme ? 0 : 1)
            ->toArray();
    }

    public function getCurrentTheme(): string
    {
        return $this->theme;
    }

    public function getAvailableThemes(): array
    {
        return $this->themes;
    }

    public function getComponentPrefix(): string
    {
        return trim(InspireCmsConfig::get('template.component_prefix', 'inspirecms'));
    }

    public function isThemeExists(string $theme): bool
    {
        // check view.components directory
        
        return is_dir(resource_path("views/components/{$theme}"));
    }

    public function getComponentWithTheme(string $component, ?string $theme = null): string
    {
        $componentPrefix = static::getComponentPrefix();

        $theme ??= static::getCurrentTheme();

        return str($theme)
            ->when(filled($componentPrefix), fn ($str) => $str->prepend($componentPrefix . '.'))
            ->finish('.')
            ->finish(trim($component))
            ->toString();
    }

    public function getComponentDirectoryForTheme(?string $theme = null): string
    {
        $componentPrefix = static::getComponentPrefix();

        $theme ??= static::getCurrentTheme();
        
        return str($theme)
            ->when(filled($componentPrefix), fn ($str) => $str->prepend($componentPrefix . '.'))
            ->finish('.')
            ->toString();
    }

    /** {@inheritDoc} */
    public function assignDefaultTemplateIfNotSet($templateable, $template)
    {
        if (is_null($templateable->getDefaultTemplate())) {
            $templateable->setAsDefaultTemplate($template);
        }
    }

    /** {@inheritDoc} */
    public function retrieveDefaultContent()
    {
        return <<<'HTML'
        @php
            $locale ??= $content->getLocale();
        @endphp
        <x-cms-template :content="$content" type="page">
            Your content here
        </x-cms-template>
        HTML;
    }

    /**
     * @param  Model & Template  $template
     */
    public function exportTemplate($template, ?string $theme = null): void
    {
        $filename = "{$template->slug}.blade.php";

        $path = str($this->getExportedTemplateDir())
            ->rtrim('/')
            ->finish('/')
            ->finish(trim($theme ?? $this->getCurrentTheme()) . '/' . $filename);

        $dir = dirname($path);
        // create directory if not exists
        FileHelper::ensureDirectoryExists($dir);

        file_put_contents($path, $template->content);
    }

    public function getExportedTemplateDir(): string
    {
        return InspireCmsConfig::get('template.exported_template_dir', resource_path('views/inspirecms/templates'));
    }

    protected static function ensureTemplateNameFormat(string $slug): string
    {
        return str($slug)
            ->trim()->trim('.')->trim('/')
            ->snake()
            ->replace(['-', ' '], '-')
            ->toString();
    }
}
