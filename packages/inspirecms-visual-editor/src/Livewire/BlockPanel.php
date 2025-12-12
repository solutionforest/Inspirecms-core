<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCmsVisualEditor\Models\BlockTemplate;

class BlockPanel extends Component
{
    public string $search = '';

    public ?string $activeCategory = null;

    public string $tab = 'blocks'; // blocks, templates, saved

    #[Computed]
    public function categories(): array
    {
        return BlockRegistry::getBlocksForPanel();
    }

    #[Computed]
    public function filteredCategories(): array
    {
        if (empty($this->search)) {
            return $this->categories;
        }

        $search = strtolower($this->search);

        return collect($this->categories)
            ->map(function ($category) use ($search) {
                $category['blocks'] = array_filter(
                    $category['blocks'],
                    fn ($block) => str_contains(strtolower($block['label']), $search) ||
                                  str_contains(strtolower($block['description'] ?? ''), $search)
                );

                return $category;
            })
            ->filter(fn ($category) => ! empty($category['blocks']))
            ->values()
            ->toArray();
    }

    #[Computed]
    public function savedTemplates(): array
    {
        return BlockTemplate::query()
            ->where(function ($query) {
                $query->where('is_public', true)
                    ->orWhere('created_by', auth()->id());
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($template) => [
                'id' => $template->id,
                'name' => $template->name,
                'type' => $template->type,
                'category' => $template->category,
                'thumbnail' => $template->thumbnail,
                'isGlobal' => $template->is_global,
            ])
            ->toArray();
    }

    public function addBlock(string $type): void
    {
        $this->dispatch('add-block', type: $type)->to("visual-editor");
    }

    public function addTemplate(string $templateId): void
    {
        $template = BlockTemplate::find($templateId);

        if ($template) {
            $blockData = $template->createInstance();
            $this->dispatch('add-block-data', blockData: $blockData)->to("visual-editor");
        }
    }

    public function setActiveCategory(?string $category): void
    {
        $this->activeCategory = $category;
    }

    public function render(): View
    {
        return view('visual-editor::livewire.block-panel');
    }
}
