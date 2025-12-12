<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use SolutionForest\InspireCms\VisualEditor\AI\Contracts\AIProviderInterface;
use SolutionForest\InspireCms\VisualEditor\AI\Providers\AnthropicProvider;
use SolutionForest\InspireCms\VisualEditor\AI\Providers\OpenAIProvider;
use SolutionForest\InspireCms\VisualEditor\AI\Services\LayoutGeneratorService;
use SolutionForest\InspireCms\VisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\ButtonBlock;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\ColumnBlock;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\ContainerBlock;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\DividerBlock;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\GridBlock;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\HeadingBlock;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\ImageBlock;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\SectionBlock;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\SpacerBlock;
use SolutionForest\InspireCms\VisualEditor\Blocks\Types\TextBlock;
use SolutionForest\InspireCms\VisualEditor\Livewire\AIAssistant;
use SolutionForest\InspireCms\VisualEditor\Livewire\BlockPanel;
use SolutionForest\InspireCms\VisualEditor\Livewire\LayersPanel;
use SolutionForest\InspireCms\VisualEditor\Livewire\SettingsPanel;
use SolutionForest\InspireCms\VisualEditor\Livewire\VisualEditor;

class VisualEditorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register AI providers
        $this->app->singleton(OpenAIProvider::class);
        $this->app->singleton(AnthropicProvider::class);

        // Register AI provider interface with default
        $this->app->bind(AIProviderInterface::class, function ($app) {
            $provider = config('inspirecms.visual_editor.ai.provider', 'anthropic');

            return match ($provider) {
                'openai' => $app->make(OpenAIProvider::class),
                'anthropic' => $app->make(AnthropicProvider::class),
                default => $app->make(AnthropicProvider::class),
            };
        });

        // Register Layout Generator Service
        $this->app->singleton(LayoutGeneratorService::class);
    }

    public function boot(): void
    {
        $this->registerBlocks();
        $this->registerLivewireComponents();
        $this->registerAssets();
    }

    protected function registerBlocks(): void
    {
        // Register core blocks
        BlockRegistry::registerMany([
            // Layout blocks
            ContainerBlock::class,
            SectionBlock::class,
            GridBlock::class,
            ColumnBlock::class,
            SpacerBlock::class,
            DividerBlock::class,

            // Basic blocks
            HeadingBlock::class,
            TextBlock::class,
            ButtonBlock::class,
            ImageBlock::class,
        ]);

        // Allow additional blocks to be registered via config
        $additionalBlocks = config('inspirecms.visual_editor.blocks', []);
        if (! empty($additionalBlocks)) {
            BlockRegistry::registerMany($additionalBlocks);
        }
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('inspirecms-visual-editor', VisualEditor::class);
        Livewire::component('inspirecms-visual-editor-block-panel', BlockPanel::class);
        Livewire::component('inspirecms-visual-editor-settings-panel', SettingsPanel::class);
        Livewire::component('inspirecms-visual-editor-layers-panel', LayersPanel::class);
        Livewire::component('inspirecms-visual-editor-ai-assistant', AIAssistant::class);
    }

    protected function registerAssets(): void
    {
        FilamentAsset::register([
            Css::make('visual-editor', __DIR__ . '/../../resources/dist/visual-editor.css')->loadedOnRequest(),
            AlpineComponent::make('visual-editor', __DIR__ . '/../../resources/dist/visual-editor.js')->loadedOnRequest(),
        ], 'solution-forest/inspirecms-visual-editor');
    }
}
