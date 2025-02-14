<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Acitons;

use Closure;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

class BackActon extends Action
{
    protected ?Closure $urlParametersUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'back';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->color('gray');

        $this->iconButton();

        $this->icon('heroicon-o-chevron-left');

        $this->label(__('inspirecms::resources/content.actions.back.label'));

        $this->url(function (null | Model | Content $record, $livewire) {

            if (! $record || ! $record instanceof Content) {
                return null;
            }

            $resource = InspireCmsConfig::getFilamentResource('page', PageResource::class);

            if ($record->trashed()) {
                return FilamentResourceHelper::attemptToGetUrl($resource, ['trash', 'index'], [], false);
            }

            if (! $record->parent?->documentType?->show_as_table) {
                return null;
            }

            $translatableLocale = method_exists($livewire, 'getActiveActionsLocale') ? $livewire->getActiveActionsLocale() : null;

            $parameters = [
                'record' => $record->parent,
                'activeRelationManager' => 0,
                // Set the locale as query parameter as \SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait
                'locale' => $translatableLocale,
            ];

            if ($this->urlParametersUsing != null) {
                $parameters = array_merge($parameters, $this->evaluate($this->urlParametersUsing, ['parameters' => $parameters, 'record' => $record]));
            }

            return FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view'], $parameters, true);
        });

        $this->visible(function (Action $action) {
            return filled($action->getUrl());
        });
    }

    public function urlParametersUsing(Closure $callback): static
    {
        $this->urlParametersUsing = $callback;

        return $this;
    }
}
