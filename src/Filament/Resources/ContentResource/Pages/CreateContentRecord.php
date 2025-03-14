<?php

namespace SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages;

use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\InspireCmsConfig;

use function Filament\Support\is_app_url;

class CreateContentRecord extends BaseContentCreatePage
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
        return InspireCmsConfig::getFilamentResource('content', ContentResource::class);
    }

    /** * {@inheritDoc} */
    public function getDocumentType()
    {
        if ($this->documentTypeRecord) {
            return $this->documentTypeRecord;
        }
        if (! $this->documentType instanceof Model) {
            return $this->documentTypeRecord = InspireCmsConfig::getDocumentTypeModelClass()::find($this->documentType);
        }

        return $this->documentType;
    }
}
