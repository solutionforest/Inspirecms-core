<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\KeyValueCache;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplateManager implements TemplateManagerInterface
{
    protected ?string $theme = null;

    protected array $themes = [];
    
    public function __construct()
    {
        $this->loadCurrentTheme();
        $this->themes = collect(InspireCmsConfig::get('template.themes', []))
            // sort the default theme to the top
            ->sortBy(fn ($value, $key) => $key === $this->theme ? 0 : 1)
            ->toArray();
    }

    public function getCurrentTheme(): ?string
    {
        $this->loadCurrentTheme();

        return $this->theme;
    }

    public function getAvailableThemes(): array
    {
        return $this->themes;
    }

    public function clearCurrentThemeCache(): void
    {
        KeyValueCache::forget(TemplateHelper::getCurrentThemeKey());
    }

    public function resetCurrentTheme(): void
    {
        $this->theme = null;
        $this->clearCurrentThemeCache();
    }

    public function getComponentPrefix(): string
    {
        return trim(InspireCmsConfig::get('template.component_prefix', 'inspirecms'));
    }

    public function isThemeExists(string $theme): bool
    {
        $defaultLayoutComponentFullPath = static::getThemeDefaultLayoutPath($theme);

        return file_exists($defaultLayoutComponentFullPath);
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

    public function getComponentDirectoryForTheme(?string $theme = null, ?string $component = null): string
    {
        $componentPrefix = static::getComponentPrefix();

        $theme ??= static::getCurrentTheme();
        
        $relativePath = str($theme)
            ->when(filled($componentPrefix), fn ($str) => $str->prepend($componentPrefix . '.'))
            ->trim('.')
            ->replace('.', '/')
            ->trim('/')
            ->when(filled($component), fn ($str) => $str
                ->finish('/')
                ->finish(str($component)->trim()->trim('.')->finish('.blade.php'))
            )
            ->toString();

        return resource_path("views/components/{$relativePath}");
    }

    public function getThemeDefaultLayoutPath(?string $theme = null): string
    {
        return static::getComponentDirectoryForTheme($theme, static::getDefaultLayoutComponentName());
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
        $componentName = static::getDefaultLayoutComponentName();
        return <<<HTML
        @php
            \$locale ??= \$content->getLocale();
        @endphp
        <x-cms-template :content="\$content" type="{$componentName}">
            Your content here
        </x-cms-template>
        HTML;
    }

    /**
     * @param  Model & Template  $template
     */
    public function exportTemplate($template, ?string $theme = null): void
    {
        $basename = trim($template->slug);

        $filename = "{$basename}.blade.php";

        $theme ??= $this->getCurrentTheme();

        $fullPath = str($this->getExportedTemplateDir())
            ->rtrim('/')
            ->finish('/')
            ->finish(trim($theme) . '/' . $filename);

        // create directory if not exists
        FileHelper::ensureDirectoryExists(dirname($fullPath));

        $themeContent = $template->getContent($theme);

        if (filled($themeContent)) {
            file_put_contents($fullPath, $themeContent);
        }
    }

    public function getExportedTemplateDir(): string
    {
        return InspireCmsConfig::get('template.exported_template_dir', resource_path('views/inspirecms/templates'));
    }

    public function ensureThemeLayoutComponentExists(string $theme, ?string $component = null): void
    {
        $component ??= static::getDefaultLayoutComponentName();

        $fullPath = $this->getComponentDirectoryForTheme($theme, $component);

        // create directory if not exists
        FileHelper::ensureDirectoryExists(dirname($fullPath));

        if (!file_exists($fullPath)) {
            $content = $this->retrieveDefaultLayoutContent();

            file_put_contents($fullPath, $content);
        }
    }

    protected static function ensureTemplateNameFormat(string $slug): string
    {
        return str($slug)
            ->trim()->trim('.')->trim('/')
            ->snake()
            ->replace(['-', ' '], '-')
            ->toString();
    }

    private static function getDefaultLayoutComponentName(): string
    {
        return 'page';
    }

    private function loadCurrentTheme()
    {
        if ($this->theme) {
            return;
        }

        $key = TemplateHelper::getCurrentThemeKey();
        $value = KeyValueCache::get($key);

        if (is_string($value) && filled($value)) {
            $this->theme = $value;
        }
    }

    private function retrieveDefaultLayoutContent()
    {
        return <<<'HTML'
        @php
            $locale ??= $content->getLocale() ?? request()->getLocale();
            $seo = $content->getSeo()?->getHtml();
            $title = $content->getTitle();
        @endphp
        <html lang="{{ $locale }}">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{{ $title }}</title>
            {!! $seo !!}
        </head>
        <body>

            {{ $slot }}

        </body>
        </html>
        HTML;
    }
}
