<?php

namespace SolutionForest\InspireCms\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;

class JsonEntry extends Entry
{
    protected string $view = 'inspirecms::filament.infolists.entries.json-entry';

    protected string $aceUrl = '';

    protected string $basePath = '';

    protected array $extensions;

    protected array $config;

    protected array $editorOptions;

    protected string $theme = '';

    protected string $darkTheme;

    protected bool $disableDarkTheme;

    protected ?string $height = '16rem';

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeConfigurations();

        $this->editorOptions(['mode' => "ace/mode/json"]);
    }

    protected function initializeConfigurations(): void
    {
        $this->aceUrl = rtrim(config('filament-ace-editor.base_url'), '/') . '/' . ltrim(config('filament-ace-editor.file'), '/');
        $this->basePath = config('filament-ace-editor.base_url');
        $this->extensions = config('filament-ace-editor.enabled_extensions');
        $this->config = config('filament-ace-editor.editor_config');
        $this->editorOptions = config('filament-ace-editor.editor_options');
        $this->disableDarkTheme = !config('filament-ace-editor.dark_mode.enable');
        $this->darkTheme = config('filament-ace-editor.dark_mode.theme');
    }

    public function theme(string $theme): static
    {
        $this->theme = "ace/theme/$theme";

        return $this->editorOptions(["theme" => $this->theme]);
    }

    public function darkTheme(string $darkTheme): static
    {
        $this->darkTheme = "ace/theme/$darkTheme";

        return $this;
    }

    public function disableDarkTheme(): static
    {
        $this->disableDarkTheme = true;
        return $this;
    }


    public function editorOptions(array $options): static
    {
        $this->editorOptions = array_merge($this->editorOptions, $options);

        return $this;
    }

    public function editorConfig(array $config): static
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function height(string $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getAceUrl(): string
    {
        return $this->aceUrl;
    }

    /**
     * Retrieves URLs for enabled extensions based on configuration.
     *
     * @return array An associative array of enabled extension URLs.
     */
    public function getEnabledExtensions(): array
    {
        $extensionsUrls = collect(config('filament-ace-editor.extensions'));
        $enabledExtensionsKeys = collect($this->extensions)->flip();
        $enabledExtensions = $extensionsUrls->intersectByKeys($enabledExtensionsKeys);
        return $enabledExtensions->toArray();
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getConfig(): array
    {
        $config = [
            "basePath" => $this->getBasePath(),
        ];

        $config = array_merge($this->config, $config);

        return $config;
    }

    public function getEditorOptions(): array
    {
        $editorOptions = [
            "readOnly" => true,
        ];

        $editorOptions = array_merge($this->editorOptions, $editorOptions);

        return $editorOptions;
    }

    public function getDarkTheme(): ?string
    {
        return $this->darkTheme;
    }

    public function isDisableDarkTheme(): bool
    {
        return $this->disableDarkTheme;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }
}
