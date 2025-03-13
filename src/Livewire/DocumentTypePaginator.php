<?php

namespace SolutionForest\InspireCms\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\SearchHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypePaginator extends \Livewire\Component
{
    use WithPagination;
    use WithoutUrlPagination;

    public $search = '';

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

    public int | string $perPage = 10;

    protected static string $view = 'inspirecms::livewire.document-type-paginator';
    
    public const LOADING_TARGETS = [
        'gotoPage',
        'nextPage',
        'previousPage',
        'search',
    ];

    public const PAGE_NAME = 'documentTypesPage';

    public function mount()
    {
        $this->resetPage(static::PAGE_NAME);
    }

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
                        'icon' => $documentType->icon,
                        'url' => $url,
                        'rawLabel' => $documentType->title,
                        'displayLabel' => UIHelper::generateTextWithDescription(
                            text: $documentType->title,
                            description: $documentType->slug ?? null,
                        ),
                    ];
                })
        );

        return view(static::$view, [
            'paginator' => $documentTypes,
            'perPage' => $this->perPage,
            'loadingTargets' => implode(', ', static::LOADING_TARGETS),
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage(static::PAGE_NAME);
    }

    /**
     * @return \Illuminate\Pagination\Paginator
     */
    protected function getDocumentTypes()
    {
        /**
         * @var Builder $query
         */
        $query = InspireCmsConfig::getDocumentTypeModelClass()::query()
            ->whereCanBeContent();

        if ($this->parentDocumentTypeId !== null) {
            $query
                // Skip self
                ->whereKeyNot($this->parentDocumentTypeId)
                ->whereHas(
                    'allowingDocumentTypes',
                    fn ($query) => $query->whereKey($this->parentDocumentTypeId)
                );
        } 
        // Is root
        else {
            $query->where('show_at_root', true);
        }

        if (filled($this->search)) {
            $query = SearchHelper::filterBySearch(
                query: $query, 
                search: $this->search,
                searchColumns: ['title', 'slug'],
                isForcedCaseInsensitive: true
            );
        }

        return $query
            ->paginate(
                perPage: $this->perPage === 'all' ? null : $this->perPage,
                pageName: static::PAGE_NAME,
                page: $this->getPage(static::PAGE_NAME),
            );
    }

    /**
     * @return class-string<\Filament\Resources\Resource>
     */
    protected static function getResource()
    {
        return InspireCmsConfig::getFilamentResource('page', PageResource::class);
    }
}
