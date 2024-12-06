<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;

use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Concerns\DocumentTypeDetailTrait;

class EditDocumentType extends BaseEditPage
{
    use DocumentTypeDetailTrait;
    use HasPreviewModal;

    public function boot()
    {
        \Pboivin\FilamentPeek\Support\Panel::ensurePluginIsLoaded();
        \Pboivin\FilamentPeek\Support\Page::ensurePreviewModalSupport($this);
        \Pboivin\FilamentPeek\Support\View::setupPreviewModal();
        \Pboivin\FilamentPeek\Support\View::setupBuilderEditor();
    }

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.document_type', DocumentTypeResource::class);
    }
}
