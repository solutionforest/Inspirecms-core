<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use SolutionForest\InspireCmsVisualEditor\AI\Services\LayoutGeneratorService;
use SolutionForest\InspireCmsVisualEditor\Models\AIGenerationHistory;

class AIAssistant extends Component
{
    public string $prompt = '';

    public string $mode = 'generate'; // generate, suggest, edit

    public bool $isLoading = false;

    public array $suggestions = [];

    public array $history = [];

    public ?string $selectedTemplate = null;

    public array $templateOptions = [
        'landing' => 'Landing Page',
        'about' => 'About Page',
        'contact' => 'Contact Page',
        'blog' => 'Blog Layout',
        'portfolio' => 'Portfolio',
        'product' => 'Product Page',
        'pricing' => 'Pricing Page',
        'features' => 'Features Page',
    ];

    public array $styleOptions = [
        'modern' => 'Modern',
        'minimal' => 'Minimal',
        'bold' => 'Bold',
        'elegant' => 'Elegant',
        'playful' => 'Playful',
        'corporate' => 'Corporate',
    ];

    public string $selectedStyle = 'modern';

    public function mount(): void
    {
        $this->loadHistory();
    }

    protected function loadHistory(): void
    {
        $this->history = AIGenerationHistory::query()
            ->where('user_id', auth()->id())
            ->where('type', 'layout')
            ->where('status', 'success')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'prompt' => $item->prompt,
                'created_at' => $item->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    #[Computed]
    public function quickPrompts(): array
    {
        return [
            'Create a hero section with a headline, description, and call-to-action button',
            'Add a features grid with 3 columns showing icons and descriptions',
            'Create a testimonials section with customer quotes',
            'Add a pricing table with 3 tiers',
            'Create a contact form section',
            'Add a team members grid with photos and bios',
            'Create a FAQ accordion section',
            'Add a newsletter signup section',
        ];
    }

    public function useQuickPrompt(string $prompt): void
    {
        $this->prompt = $prompt;
    }

    public function generateLayout(): void
    {
        if (empty($this->prompt) && empty($this->selectedTemplate)) {
            Notification::make()
                ->title('Please enter a prompt or select a template')
                ->warning()
                ->send();

            return;
        }

        $this->isLoading = true;

        try {
            $service = app(LayoutGeneratorService::class);

            $fullPrompt = $this->buildPrompt();

            $result = $service->generate($fullPrompt, [
                'template' => $this->selectedTemplate,
                'style' => $this->selectedStyle,
            ]);

            if ($result['success']) {
                $this->dispatch('apply-ai-layout', layoutData: $result['layout'])->to('visual-editor');

                Notification::make()
                    ->title('Layout generated successfully')
                    ->success()
                    ->send();

                // Update history
                $this->loadHistory();

                // Clear prompt
                $this->prompt = '';
            } else {
                Notification::make()
                    ->title('Failed to generate layout')
                    ->body($result['error'] ?? 'Unknown error')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error generating layout')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isLoading = false;
        }
    }

    protected function buildPrompt(): string
    {
        $prompt = $this->prompt;

        if ($this->selectedTemplate) {
            $templateName = $this->templateOptions[$this->selectedTemplate] ?? $this->selectedTemplate;
            $prompt = "Create a {$templateName} layout. " . $prompt;
        }

        $styleName = $this->styleOptions[$this->selectedStyle] ?? $this->selectedStyle;
        $prompt .= " Use a {$styleName} design style.";

        return $prompt;
    }

    public function suggestNext(): void
    {
        $this->isLoading = true;

        try {
            $service = app(LayoutGeneratorService::class);

            $result = $service->suggestNextBlocks([
                'prompt' => 'Suggest what blocks could be added next based on the current layout',
            ]);

            if ($result['success']) {
                $this->suggestions = $result['suggestions'] ?? [];
            } else {
                Notification::make()
                    ->title('Failed to get suggestions')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error getting suggestions')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isLoading = false;
        }
    }

    public function applySuggestion(array $suggestion): void
    {
        $this->dispatch('add-block-data', blockData: $suggestion['blockData'])->to('visual-editor');

        Notification::make()
            ->title('Block added')
            ->success()
            ->send();
    }

    public function useHistoryItem(string $historyId): void
    {
        $item = AIGenerationHistory::find($historyId);

        if ($item && isset($item->result['layout'])) {
            $this->dispatch('apply-ai-layout', layoutData: $item->result['layout'])->to('visual-editor');

            Notification::make()
                ->title('Layout applied from history')
                ->success()
                ->send();
        }
    }

    public function render(): View
    {
        return view('visual-editor::livewire.ai-assistant');
    }
}
