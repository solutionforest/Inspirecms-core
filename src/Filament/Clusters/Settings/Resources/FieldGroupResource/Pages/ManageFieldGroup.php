<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\Pages;

use Filament\Actions;
use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroupResource\Pages\ManageFieldGroup as BasePage;
use SolutionForest\InspireCms\Filament\Actions\QuickCreateAction;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\Filament\Resources\Concerns\HasCloneAction;
use SolutionForest\InspireCms\Filament\Resources\Concerns\HasQuickCreateAction;
use SolutionForest\InspireCms\Filament\Resources\Concerns\HasQuickEditAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;

class ManageFieldGroup extends BasePage
{
    use HasCloneAction;
    use HasQuickCreateAction;
    use HasQuickEditAction;

    protected function getHeaderActions(): array
    {
        return [
            QuickCreateAction::make(),
        ];
    }

    /** {@inheritDoc} */
    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
            ...(filled($breadcrumb = $this->getBreadcrumb()) ? [$breadcrumb] : []),
        ];

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.field_group', FieldGroupResource::class);
    }

    protected function configureAction(Actions\Action $action): void
    {
        match (true) {
            $action instanceof QuickCreateAction => $this->configureQuickCreateAction($action),
            default => parent::configureAction($action),
        };
    }

    protected function configureTableAction(\Filament\Tables\Actions\Action $action): void
    {
        match (true) {
            $action instanceof CloneAction => $this->configureCloneAction($action),
            $action instanceof QuickEditAction => $this->configureQuickEditAction($action),
            default => parent::configureTableAction($action),
        };
    }
}
