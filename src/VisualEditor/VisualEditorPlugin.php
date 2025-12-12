<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor;

use Filament\Contracts\Plugin;
use Filament\Panel;
use SolutionForest\InspireCms\VisualEditor\Filament\Pages\VisualEditorPage;
use SolutionForest\InspireCms\VisualEditor\Filament\Resources\BlockTemplateResource;

class VisualEditorPlugin implements Plugin
{
    protected bool $hasBlockTemplateResource = true;

    protected bool $hasVisualEditorPage = true;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'inspirecms-visual-editor';
    }

    public function register(Panel $panel): void
    {
        $pages = [];
        $resources = [];

        if ($this->hasVisualEditorPage) {
            $pages[] = VisualEditorPage::class;
        }

        if ($this->hasBlockTemplateResource) {
            $resources[] = BlockTemplateResource::class;
        }

        $panel
            ->pages($pages)
            ->resources($resources);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function blockTemplateResource(bool $condition = true): static
    {
        $this->hasBlockTemplateResource = $condition;

        return $this;
    }

    public function visualEditorPage(bool $condition = true): static
    {
        $this->hasVisualEditorPage = $condition;

        return $this;
    }
}
