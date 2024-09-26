<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ConfigureContentResourcePageSubNavigation;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

use function Filament\Support\is_app_url;

class CreatePage extends BaseContentCreatePage
{
    use ConfigureContentResourcePageSubNavigation;
    use ContentPageTrait;

    #[Url]
    public $documentType = null;

    #[Locked]
    public ?Model $documentTypeRecord = null;

    public function booted()
    {
        if (! $this->documentTypeRecord) {
            $this->documentTypeRecord = InspireCmsConfig::getDocumentTypeModelClass()::find($this->documentType);
        }

        if (! $this->documentTypeRecord || blank($this->documentType)) {
            $redirectUrl = static::getResource()::getUrl('index');
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
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

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        $record = $this->getRecord();

        if (! $record) {
            return $resource::getUrl('index');
        }

        $parent = $record?->parent;

        if ($parent) {
            return $resource::getUrl('index', ['parent' => $parent]);
        }

        return $resource::getUrl('edit', ['record' => $record]);
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $parent = $this->getParentRecord();

        $breadcrumbs = [];

        foreach ($parent?->ancestors() ?? [] as $ancestor) {
            $url = null;
            if ($resource::hasPage('view') && $resource::canView($ancestor)) {
                $url = $resource::getUrl('view', ['record' => $ancestor]);
            } elseif ($resource::hasPage('edit') && $resource::canEdit($ancestor)) {
                $url = $resource::getUrl('edit', ['record' => $ancestor]);
            }

            $parentTitle = $resource::getRecordTitle($ancestor) ?? $ancestor->getKey();

            if ($url) {
                $breadcrumbs[$url] = $parentTitle;
            } else {
                $breadcrumbs[] = $parentTitle;
            }
        }

        $breadcrumbs[] = $this->getBreadcrumb();

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    public function getParentTitle(): ?string
    {
        $title = null;
        if ($parent = $this->getRecord()?->parent) {
            $title = static::getResource()::getRecordTitle($parent);
        }

        return $title;
    }

    public function getDocumentType(): int | Model | string
    {
        return $this->documentTypeRecord ?? $this->documentType;
    }
}
