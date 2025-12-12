<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use SolutionForest\InspireCmsVisualEditor\Models\BlockTemplate;

class TemplatesPanel extends Component
{
    public string $search = '';

    public ?string $activeCategory = null;

    public bool $showSaveModal = false;

    public bool $showPreviewModal = false;

    public ?string $previewTemplateId = null;

    // Save template form data
    public string $templateName = '';

    public string $templateDescription = '';

    public string $templateCategory = 'custom';

    public bool $templateIsGlobal = false;

    public bool $templateIsPublic = false;

    public array $templateTags = [];

    public ?array $blockToSave = null;

    #[Computed]
    public function categories(): array
    {
        return BlockTemplate::getCategories();
    }

    #[Computed]
    public function templates(): array
    {
        return BlockTemplate::query()
            ->accessibleBy(auth()->id())
            ->search($this->search)
            ->when($this->activeCategory, fn ($query) => $query->category($this->activeCategory))
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->groupBy('category')
            ->toArray();
    }

    #[Computed]
    public function flatTemplates(): array
    {
        return BlockTemplate::query()
            ->accessibleBy(auth()->id())
            ->search($this->search)
            ->when($this->activeCategory, fn ($query) => $query->category($this->activeCategory))
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn ($template) => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'type' => $template->type,
                'category' => $template->category,
                'thumbnail' => $template->thumbnail,
                'is_global' => $template->is_global,
                'is_public' => $template->is_public,
                'tags' => $template->tags ?? [],
                'block_data' => $template->block_data,
                'created_at' => $template->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    #[Computed]
    public function previewTemplate(): ?array
    {
        if (!$this->previewTemplateId) {
            return null;
        }

        $template = BlockTemplate::find($this->previewTemplateId);
        if (!$template) {
            return null;
        }

        return [
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'type' => $template->type,
            'category' => $template->category,
            'thumbnail' => $template->thumbnail,
            'is_global' => $template->is_global,
            'block_data' => $template->block_data,
            'created_at' => $template->created_at->format('M d, Y'),
            'creator' => $template->creator?->name ?? 'Unknown',
        ];
    }

    public function setActiveCategory(?string $category): void
    {
        $this->activeCategory = $this->activeCategory === $category ? null : $category;
    }

    #[On('save-as-template')]
    public function openSaveModal(array $blockData): void
    {
        $this->blockToSave = $blockData;
        $this->templateName = $blockData['type'] ?? 'Block Template';
        $this->templateDescription = '';
        $this->templateCategory = 'custom';
        $this->templateIsGlobal = false;
        $this->templateIsPublic = false;
        $this->templateTags = [];
        $this->showSaveModal = true;
    }

    public function closeSaveModal(): void
    {
        $this->showSaveModal = false;
        $this->blockToSave = null;
        $this->resetTemplateForm();
    }

    public function saveTemplate(): void
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
            'templateDescription' => 'nullable|string|max:1000',
            'templateCategory' => 'required|string|max:50',
            'blockToSave' => 'required|array',
        ]);

        BlockTemplate::create([
            'name' => $this->templateName,
            'description' => $this->templateDescription,
            'type' => $this->blockToSave['type'] ?? 'unknown',
            'category' => $this->templateCategory,
            'block_data' => $this->blockToSave,
            'tags' => $this->templateTags,
            'is_global' => $this->templateIsGlobal,
            'is_public' => $this->templateIsPublic,
            'created_by' => auth()->id(),
        ]);

        $this->closeSaveModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('visual-editor::visual-editor.templates.saved'),
        ]);
    }

    public function useTemplate(string $templateId): void
    {
        $template = BlockTemplate::find($templateId);

        if ($template) {
            $blockData = $template->createInstance();
            $this->dispatch('add-block-data', blockData: $blockData)->to('visual-editor');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('visual-editor::visual-editor.templates.inserted'),
            ]);
        }
    }

    public function previewTemplate(string $templateId): void
    {
        $this->previewTemplateId = $templateId;
        $this->showPreviewModal = true;
    }

    public function closePreviewModal(): void
    {
        $this->showPreviewModal = false;
        $this->previewTemplateId = null;
    }

    public function deleteTemplate(string $templateId): void
    {
        $template = BlockTemplate::find($templateId);

        if ($template && $template->created_by === auth()->id()) {
            $template->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('visual-editor::visual-editor.templates.deleted'),
            ]);
        }
    }

    public function duplicateTemplate(string $templateId): void
    {
        $template = BlockTemplate::find($templateId);

        if ($template) {
            BlockTemplate::create([
                'name' => $template->name . ' (Copy)',
                'description' => $template->description,
                'type' => $template->type,
                'category' => $template->category,
                'block_data' => $template->block_data,
                'tags' => $template->tags,
                'is_global' => false,
                'is_public' => false,
                'created_by' => auth()->id(),
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('visual-editor::visual-editor.templates.duplicated'),
            ]);
        }
    }

    protected function resetTemplateForm(): void
    {
        $this->templateName = '';
        $this->templateDescription = '';
        $this->templateCategory = 'custom';
        $this->templateIsGlobal = false;
        $this->templateIsPublic = false;
        $this->templateTags = [];
    }

    public function render(): View
    {
        return view('visual-editor::livewire.templates-panel');
    }
}
