<?php

namespace SolutionForest\InspireCms\Livewire\Components;

use Livewire\Attributes\Locked;
use Livewire\Component;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class PreviewContent extends Component
{
    #[Locked]
    public string | int $content;

    public function mount(string | int $content) {}

    public function getDto(): ?ContentDto
    {
        $record = InspireCmsConfig::getContentModelClass()::with([
            'documentType.templates' => fn ($query) => $query->wherePivot('is_default', true),
            'templates' => fn ($query) => $query->wherePivot('is_default', true),
        ])->find($this->content);

        if (! $record) {
            return null;
        }

        return ContentDto::fromModel($record);
    }

    public function render()
    {
        $dto = $this->getDto();

        $view = $dto->getDefaultTemplate()?->viewName ?? 'inspirecms::livewire.preview-content-placeholder';

        return view($view)
            ->layout('inspirecms::livewire.layout.base');
    }
}
