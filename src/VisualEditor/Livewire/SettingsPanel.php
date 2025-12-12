<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Livewire;

use Filament\Forms\Components\Tabs;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use SolutionForest\InspireCms\VisualEditor\Blocks\Registry\BlockRegistry;

class SettingsPanel extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $blockId = null;

    public ?string $blockType = null;

    public ?array $blockData = null;

    public ?array $formData = null;

    public string $activeTab = 'settings'; // settings, styles

    public function mount(): void
    {
        $this->formData = [];
    }

    #[On('block-selected')]
    public function loadBlock(?string $blockId, ?array $blockData): void
    {
        $this->blockId = $blockId;
        $this->blockData = $blockData;
        $this->blockType = $blockData['type'] ?? null;

        if ($blockData) {
            $this->formData = [
                'props' => $blockData['props'] ?? [],
                'styles' => $blockData['styles'] ?? [],
            ];

            $this->form->fill($this->formData);
        } else {
            $this->formData = [];
        }
    }

    #[Computed]
    public function block()
    {
        if (! $this->blockType) {
            return null;
        }

        return BlockRegistry::get($this->blockType);
    }

    public function form(Form $form): Form
    {
        if (! $this->block) {
            return $form->schema([]);
        }

        $settingsSchema = $this->block->getSettingsSchema();
        $styleSchema = $this->block->getStyleSchema();

        return $form
            ->schema([
                Tabs::make('BlockSettings')
                    ->tabs([
                        Tabs\Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema($settingsSchema),
                        Tabs\Tab::make('Design')
                            ->icon('heroicon-o-paint-brush')
                            ->schema($styleSchema),
                    ])
                    ->contained(false),
            ])
            ->statePath('formData')
            ->live();
    }

    public function updated($property): void
    {
        if (str_starts_with($property, 'formData.')) {
            $this->saveBlock();
        }
    }

    public function saveBlock(): void
    {
        if (! $this->blockId || ! $this->formData) {
            return;
        }

        $this->dispatch('update-block', blockId: $this->blockId, updates: [
            'props' => $this->formData['props'] ?? [],
            'styles' => $this->formData['styles'] ?? [],
        ])->to(VisualEditor::class);
    }

    public function deleteBlock(): void
    {
        if (! $this->blockId) {
            return;
        }

        $this->dispatch('delete-block', blockId: $this->blockId)->to(VisualEditor::class);
        $this->blockId = null;
        $this->blockData = null;
        $this->blockType = null;
        $this->formData = [];
    }

    public function duplicateBlock(): void
    {
        if (! $this->blockId) {
            return;
        }

        $this->dispatch('duplicate-block', blockId: $this->blockId)->to(VisualEditor::class);
    }

    public function render(): View
    {
        return view('inspirecms::visual-editor.livewire.settings-panel');
    }
}
