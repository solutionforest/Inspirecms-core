<?php

namespace SolutionForest\InspireCms\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypePaginator extends \Livewire\Component
{
    use WithPagination;

    /**
     * @var null|int|string
     */
    #[Locked]
    public $parentDocumentTypeId = null;

    /**
     * @var null|string
     */
    #[Locked]
    public $parentContentId = null;

    public $translatableLocale = null;

    public function render()
    {
        $documentTypes = tap(
            $this->getDocumentTypes(),
            fn ($paginatedInstance) => $paginatedInstance
                ->getCollection()
                ->transform(function (\SolutionForest\InspireCms\Models\Contracts\DocumentType | Model $documentType) {

                    $parameters = [
                        'documentType' => $documentType->getKey(),
                        'parent' => $this->parentContentId,
                        // Set the locale as query parameter as \SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait
                        'locale' => $this->translatableLocale,
                    ];

                    $url = FilamentResourceHelper::attemptToGetUrl(
                        static::getResource(),
                        'create',
                        $parameters,
                        false
                    );

                    return [
                        'label' => $documentType->title,
                        'icon' => $documentType->icon,
                        'url' => $url,
                    ];
                })
        );

        return view('inspirecms::livewire.document-type-paginator', [
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * @return \Illuminate\Pagination\Paginator
     */
    protected function getDocumentTypes()
    {
        /**
         * @var Builder $query
         */
        $query = InspireCmsConfig::getDocumentTypeModelClass()::whereCanBeContent();

        if ($this->parentDocumentTypeId !== null) {
            $query
                ->whereKeyNot($this->parentDocumentTypeId)
                ->whereDoesntHave(
                    'rejectingDocumentTypes',
                    fn ($query) => $query->whereKey($this->parentDocumentTypeId)
                );
        }

        return $query->paginate(perPage: 15, pageName: 'documentTypesPage');
    }

    /**
     * @return class-string<\Filament\Resources\Resource>
     */
    protected static function getResource()
    {
        return InspireCmsConfig::getFilamentResource('page', PageResource::class);
    }
}
