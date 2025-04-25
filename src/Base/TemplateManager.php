<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\KeyValueCache;
use SolutionForest\InspireCms\Helpers\FileHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplateManager implements TemplateManagerInterface
{
    protected ?string $theme = null;

    public function __construct()
    {
        $this->loadCurrentTheme();
    }

    public function getCurrentTheme(): ?string
    {
        $this->loadCurrentTheme();

        return $this->theme;
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

    public function getAvailableThemes(): array
    {
        return collect($this->getAvailableThemesFromFolder())
            ->merge(TemplateHelper::getDefaultTemplateThemes())
            ->merge([$this->getCurrentTheme()])
            ->unique()->filter()->values()
            // sort the default theme to the top
            ->sortBy(fn ($theme) => $theme === $this->theme ? 0 : 1)
            ->toArray();
    }

    public function isThemeExists(string $theme): bool
    {
        return is_dir($this->getPath() . '/' . $theme);
    }

    public function hasComponent(string $componentName, ?string $theme = null): bool
    {
        $path = $this->getComponentPathWithTheme($componentName, $theme);

        return file_exists($path);
    }

    public function getPath(): string
    {
        return TemplateHelper::getDirectoryForThemedComponents();
    }

    public function getComponentWithTheme(string $componentName, ?string $theme = null): string
    {
        $componentPrefix = TemplateHelper::getComponentPrefixForThemes();

        $theme ??= static::getCurrentTheme();

        return str($theme)
            ->when(filled($componentPrefix), fn ($str) => $str->prepend($componentPrefix . '.'))
            ->finish('.')
            ->finish(trim($componentName))
            ->toString();
    }

    public function getComponentPathWithTheme(?string $componentName = null, ?string $theme = null): string
    {
        $theme ??= $this->getCurrentTheme();

        $path = $this->getPath();

        if (filled($theme)) {
            $path .= '/' . $theme;
        }

        if (filled($componentName)) {
            $path .= '/' . TemplateHelper::ensureViewFileNameForTemplate($componentName);
        }

        return $path;
    }

    public function getThemeDefaultLayoutPath(?string $theme = null): string
    {
        return static::getComponentPathWithTheme(
            componentName: TemplateHelper::getDefaultThemedLayoutComponentName(),
            theme: $theme,
        );
    }

    public function createTheme(string $theme): bool
    {
        try {

            $filePath = $this->getThemeDefaultLayoutPath($theme);

            $dir = dirname($filePath);

            if (! is_dir($dir)) {
                FileHelper::ensureDirectoryExists($dir);
            }

            if (! file_exists($filePath)) {

                $content = TemplateHelper::retrieveDefaultLayoutContent();

                file_put_contents($filePath, $content);

                return true;
            }

        } catch (\Throwable $th) {
            return false;
        }

        return false;
    }

    public function cloneTheme(string $sourceTheme, string $newTheme): bool
    {
        try {

            $sourceDir = $this->getComponentPathWithTheme(theme: $sourceTheme);
            $newDir = $this->getComponentPathWithTheme(theme: $newTheme);

            if ($newTheme != $sourceTheme && is_dir($sourceDir)) {
                FileHelper::copyDirectory($sourceDir, $newDir);

                return true;
            }

        } catch (\Throwable $th) {
            return false;
        }

        return false;
    }

    /** {@inheritDoc} */
    public function assignDefaultTemplateIfNotSet($templateable, $template)
    {
        if (is_null($templateable->getDefaultTemplate())) {
            $templateable->setAsDefaultTemplate($template);
        }
    }

    /**
     * @param  Model & Template  $template
     */
    public function exportTemplate($template, ?string $theme = null): void
    {
        $basename = trim($template->slug);

        $filename = "{$basename}.blade.php";

        $theme ??= $this->getCurrentTheme();

        $fullPath = str(TemplateHelper::getDirectoryForExportedTemplates())
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

    private function getAvailableThemesFromFolder(): array
    {
        $themes = [];

        $themeDir = TemplateHelper::getDirectoryForThemedComponents();

        if (is_dir($themeDir)) {
            $themeDirs = scandir($themeDir);

            foreach ($themeDirs as $theme) {
                if ($theme === '.' || $theme === '..') {
                    continue;
                }

                $themes[] = $theme;
            }
        }

        return array_values(array_filter(array_unique($themes)));
    }
}
