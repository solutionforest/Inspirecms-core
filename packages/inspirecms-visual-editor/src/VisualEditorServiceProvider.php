<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Livewire;
use SolutionForest\InspireCmsVisualEditor\AI\Contracts\AIProviderInterface;
use SolutionForest\InspireCmsVisualEditor\AI\Providers\AnthropicProvider;
use SolutionForest\InspireCmsVisualEditor\AI\Providers\OpenAIProvider;
use SolutionForest\InspireCmsVisualEditor\AI\Services\LayoutGeneratorService;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ButtonBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ColumnBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ContainerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\DividerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\GridBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\HeadingBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ImageBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\SectionBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\SpacerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\TextBlock;
use SolutionForest\InspireCmsVisualEditor\Livewire\AIAssistant;
use SolutionForest\InspireCmsVisualEditor\Livewire\BlockPanel;
use SolutionForest\InspireCmsVisualEditor\Livewire\LayersPanel;
use SolutionForest\InspireCmsVisualEditor\Livewire\SettingsPanel;
use SolutionForest\InspireCmsVisualEditor\Livewire\VisualEditor;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class VisualEditorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'visual-editor';

    public static string $viewNamespace = 'visual-editor';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace)
            ->hasTranslations()
            ->hasMigration('create_visual_editor_tables');
    }

    public function packageRegistered(): void
    {
        // Register AI providers
        $this->app->singleton(OpenAIProvider::class);
        $this->app->singleton(AnthropicProvider::class);

        // Register AI provider interface with default
        $this->app->bind(AIProviderInterface::class, function ($app) {
            $provider = config('visual-editor.ai.provider', 'anthropic');

            return match ($provider) {
                'openai' => $app->make(OpenAIProvider::class),
                'anthropic' => $app->make(AnthropicProvider::class),
                default => $app->make(AnthropicProvider::class),
            };
        });

        // Register Layout Generator Service
        $this->app->singleton(LayoutGeneratorService::class);
    }

    public function packageBooted(): void
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
        $additionalBlocks = config('visual-editor.blocks', []);
        if (! empty($additionalBlocks)) {
            BlockRegistry::registerMany($additionalBlocks);
        }
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('visual-editor', VisualEditor::class);
        Livewire::component('visual-editor-block-panel', BlockPanel::class);
        Livewire::component('visual-editor-settings-panel', SettingsPanel::class);
        Livewire::component('visual-editor-layers-panel', LayersPanel::class);
        Livewire::component('visual-editor-ai-assistant', AIAssistant::class);
    }

    protected function registerAssets(): void
    {
        FilamentAsset::register([
            Css::make('visual-editor-styles', __DIR__ . '/../resources/dist/visual-editor.css')->loadedOnRequest(),
            AlpineComponent::make('visual-editor-canvas', __DIR__ . '/../resources/dist/visual-editor.js')->loadedOnRequest(),
        ], 'solution-forest/inspirecms-visual-editor');
    }
}
