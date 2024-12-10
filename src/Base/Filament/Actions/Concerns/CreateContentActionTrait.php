<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

trait CreateContentActionTrait
{
    protected null | Closure | string | int $parentContentKey = null;

    protected null | Closure | string | int | Model $parentDocumentType = null;

    protected ?Closure $documentTypeTitleUsing = null;

    protected ?Closure $nodeTitleUsing = null;

    protected ?Closure $urlParametersUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'create_content';
    }

    protected function setUpAction(): void
    {
        $contentResource = InspireCmsConfig::getFilamentResource('page', PageResource::class);

        $this->authorize('create', InspireCmsConfig::getContentModelClass());

        $this->label(__('inspirecms::actions.create_content.label'));

        $this->icon('heroicon-o-plus');

        $this->hidden(fn () => ! $contentResource::can('create'));

        $this->slideOver();

        $this->modal();

        $this->modalWidth('lg');

        $this->stickyModalHeader();

        $this->modalHeading(function () {
            $title = $this->evaluate($this->nodeTitleUsing);

            if (! is_string($title)) {
                $title = null;
            }

            if (blank($title)) {
                return __('inspirecms::actions.create_content.label');
            }

            return __('inspirecms::actions.create_content.modal.heading', ['title' => $title]);
        });

        $this->modalContent(function ($livewire) use ($contentResource) {

            $translatableLocale = method_exists($livewire, 'getActiveActionsLocale') ? $livewire->getActiveActionsLocale() : null;

            return view('inspirecms::filament.actions.create-content', [
                'documentTypes' => $this->getAvailableDocumentTypes(),
                'getLabelUsing' => fn (?Model $record) => $this->evaluate($this->documentTypeTitleUsing, ['record' => $record]) ?? $record?->title,
                'getUrlUsing' => function (?Model $record) use ($contentResource, $translatableLocale) {

                    $parameters = [
                        'documentType' => $record->getKey(),
                        'parent' => $this->getParentContentKey(),
                        // Set the locale as query parameter as \SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait
                        'locale' => $translatableLocale,
                    ];

                    if ($this->urlParametersUsing != null) {
                        $parameters = array_merge($parameters, $this->evaluate($this->urlParametersUsing, ['parameters' => $parameters, 'record' => $record]));
                    }

                    return FilamentResourceHelper::attemptToGetUrl(
                        $contentResource,
                        'create',
                        $parameters,
                        false
                    );
                },
            ]);
        });

        $this->modalSubmitAction(false);
    }

    public function parentContentKey(Closure | string | int | null $parentContentKey): static
    {
        $this->parentContentKey = $parentContentKey;

        return $this;
    }

    public function parentDocumentType(Closure | string | int | Model | null $parentDocumentType): static
    {
        $this->parentDocumentType = $parentDocumentType;

        return $this;
    }

    public function documentTypeTitleUsing(Closure $callback): static
    {
        $this->documentTypeTitleUsing = $callback;

        return $this;
    }

    public function urlParametersUsing(Closure $callback): static
    {
        $this->urlParametersUsing = $callback;

        return $this;
    }

    public function getParentContentKey(): null | Closure | string | int
    {
        return $this->evaluate($this->parentContentKey);
    }

    public function getParentDocumentType(): null | Closure | string | int | Model
    {
        return $this->evaluate($this->parentDocumentType);
    }

    public function nodeTitleUsing(Closure $callback): static
    {
        $this->nodeTitleUsing = $callback;

        return $this;
    }

    /**
     * @return Collection|array
     */
    protected function getAvailableDocumentTypes()
    {
        /**
         * @var Builder $query
         */
        $query = InspireCmsConfig::getDocumentTypeModelClass()::whereIsWebPage();

        if (($parentDocumentType = $this->getParentDocumentType()) !== null) {
            $query->whereDoesntHave(
                'rejectingDocumentTypes',
                fn ($query) => $query->whereKey($parentDocumentType instanceof Model ? $parentDocumentType->getKey() : $parentDocumentType)
            );
        } 

        return $query->get();
    }
}
