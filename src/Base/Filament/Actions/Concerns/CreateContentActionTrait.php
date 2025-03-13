<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\InspireCmsConfig;

trait CreateContentActionTrait
{
    protected null | Closure | string | int $parentContentKey = null;

    protected null | Closure | string | int | Model $parentDocumentType = null;

    protected ?Closure $documentTypeTitleUsing = null;

    protected ?Closure $nodeTitleUsing = null;

    protected $page = 1;

    public static function getDefaultName(): ?string
    {
        return 'create_content';
    }

    protected function setUpAction(): void
    {
        $contentResource = InspireCmsConfig::getFilamentResource('page', PageResource::class);

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->authorize('create');

        $this->label(__('inspirecms::resources/content.actions.create_content.label'));

        $this->icon('heroicon-o-plus');

        $this->hidden(fn () => ! $contentResource::can('create'));

        $this->slideOver();

        $this->modal();

        $this->modalWidth('5xl');

        $this->stickyModalHeader();

        $this->modalHeading(function () {
            $title = $this->evaluate($this->nodeTitleUsing);

            if (! is_string($title)) {
                $title = null;
            }

            if (blank($title)) {
                return __('inspirecms::resources/content.actions.create_content.label');
            }

            return __('inspirecms::resources/content.actions.create_content.modal.heading', ['title' => $title]);
        });

        $this->modalContent(function ($livewire) {
            $parentDocumentType = $this->getParentDocumentType();
            $parentDocumentTypeId = $parentDocumentType instanceof Model ? $parentDocumentType->getKey() : $parentDocumentType;

            $translatableLocale = isset($livewire->activeLocale) ? $livewire->activeLocale : null;

            return view('inspirecms::filament.actions.create-content', [
                'parentDocumentTypeId' => $parentDocumentTypeId,
                'translatableLocale' => $translatableLocale,
                'parentContentId' => $this->getParentContentKey(),
            ]);
        });

        $this->modalSubmitAction(false);
        $this->modalCancelAction(false);
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
}
