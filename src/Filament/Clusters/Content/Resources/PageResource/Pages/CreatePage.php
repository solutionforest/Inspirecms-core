<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

use function Filament\Support\is_app_url;

class CreatePage extends BaseContentCreatePage
{
    #[Url]
    public $documentType = null;

    #[Locked]
    public ?Model $documentTypeRecord = null;

    public function mount(): void
    {
        if (! $this->documentTypeRecord) {
            $this->documentTypeRecord = InspireCmsConfig::getDocumentTypeModelClass()::find($this->documentType);
        }

        if (! $this->documentTypeRecord || blank($this->documentType)) {
            $redirectUrl = static::getResource()::getUrl('index');
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
            return;
        }

        parent::mount();
    }

    protected function fillForm(): void
    {
        $documentType = $this->getDocumentType();
        $this->form->fill([
            'document_type' => $documentType instanceof Model ? $documentType->getKey() : $documentType,
            'parent_id' => $this->getParentRecord()?->getKey(),
        ]);
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }

    public function getDocumentType(): int | Model | string
    {
        return $this->documentTypeRecord ?? $this->documentType;
    }
}
