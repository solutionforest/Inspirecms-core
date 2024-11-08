<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

class BackToParentContentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'back_to_parent_content';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->color('gray');

        $this->iconButton();

        $this->icon('heroicon-o-chevron-left');

        $this->url(function (?Model $record) {
            if ($record->trashed() || ! $record || ! ($record instanceof Content)) {
                return null;
            }
            if (! $record->parent?->documentType?->isShowChildrenAsTable()) {
                return null;
            }

            $resource = InspireCmsConfig::get('filament.resources.page', PageResource::class);

            return FilamentResourceHelper::attemptToGetUrl($resource, ['view', 'edit'], [
                'record' => $record->parent,
                'activeRelationManager' => 0,
            ], true);
        });

        $this->visible(function (Action $action) {
            return filled($action->getUrl());
        });
    }
}
