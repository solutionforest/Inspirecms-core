<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait CreateContentActionTrait
{
    protected null | Closure | string | int $parentContentKey = null;

    protected null | Closure | string | int | Model $parentDocumentType = null;

    protected ?Closure $documentTypeTitleUsing = null;

    protected ?Closure $nodeTitleUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'create_content';
    }

    protected function setUpAction(): void
    {
        $contentResource = config('inspirecms.filament.resources.page', PageResource::class);

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

        $this->modalContent(function () use ($contentResource) {

            return view('inspirecms::filament.actions.create-content', [
                'documentTypes' => $this->getAvailableDocumentTypes(),
                'getLabelUsing' => fn (?Model $record) => $this->evaluate($this->documentTypeTitleUsing, ['record' => $record]) ?? $record?->title,
                'getUrlUsing' => function (?Model $record) use ($contentResource) {
                    return FilamentResourceHelper::attemptToGetUrl(
                        $contentResource,
                        'create',
                        [
                            'documentType' => $record->getKey(),
                            'parent' => $this->getParentContentKey(),
                        ],
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
        $query = InspireCmsConfig::getDocumentTypeModelClass()::isWebPage();

        if (($parentDocumentType = $this->getParentDocumentType()) !== null) {
            $query->whereParent($parentDocumentType instanceof Model ? $parentDocumentType->getKey() : $parentDocumentType);
        } else {
            $query->whereIsRoot();
        }

        return $query->get();
    }
}
