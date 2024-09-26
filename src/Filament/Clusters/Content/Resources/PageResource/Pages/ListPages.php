<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\CreateAction as ActionsCreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCms\Filament\Actions\CreateContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ConfigureContentResourcePageSubNavigation;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentListPage;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;

class ListPages extends BaseContentListPage
{
    use ConfigureContentResourcePageSubNavigation;

    public function getActions(): array
    {
        return [
            CreateContentAction::make()
                ->modifyUrlParameterUsing(function (array $parameters) {
                    return array_merge($parameters, ['parent' => $this->getParentKey()]);
                }),
        ];
    }

    public function getHeading(): string | Htmlable
    {
        $action = collect([
            EditAction::make(),
            ViewAction::make(),
        ])->map(function ($action) {
            match (true) {
                $action instanceof EditAction => $this->configureHeadingEditAction($action),
                $action instanceof ViewAction => $this->configureHeadingViewAction($action),
            };

            return $action;
        })->first(fn ($action) => $action->isVisible());

        $heading = parent::getHeading();

        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex gap-x-2">
                <span class="flex-1">
                    {{ $heading }}
                </span>
                {{ $action }}
            </div>
        blade, ['heading' => $heading, 'action' => $action]));
    }

    public function booted(): void
    {
        $parent = $this->getParentRecord();
        if (! $parent || $parent->getLevel() != 0) {
            $cluster = static::getCluster();
            redirect($cluster::getUrl());
        }
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn ($query) => $query->where('parent_id', $this->parent ?? 0));
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumbs = [
            $resource::getUrl('index', ['parent' => $this->parent]) => $this->getTitle(),
            ...(filled($breadcrumb = $this->getBreadcrumb()) ? [$breadcrumb] : []),
        ];

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    public function getTitle(): string | Htmlable
    {
        $title = null;
        if ($parent = $this->getParentRecord()) {
            $title = static::getResource()::getRecordTitle($parent);
        }

        return $title ?? parent::getTitle();
    }

    protected function configureCreateAction(CreateAction | ActionsCreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action->modelLabel(strtolower(__('inspirecms::inspirecms.content')));

        $action->url(function () {
            $resource = static::getResource();

            return $resource::getUrl('create', ['parent' => $this->parent]);
        });
    }

    protected function configureHeadingEditAction(EditAction $action): void
    {
        $resource = static::getResource();

        if ($parent = $this->getParentRecord()) {

            $action
                ->authorize($resource::canEdit($parent))
                ->record($parent);

            if ($url = FilamentResourceHelper::attemptToGetUrl($resource, ['edit'], ['record' => $parent], false)) {
                $action->url(fn (): string => $url);
            }
        } elseif (! $resource::hasPage('edit')) {
            $action->hidden();
        }

        $action
            ->model($this->getModel())
            ->modelLabel($this->getModelLabel() ?? static::getResource()::getModelLabel())
            ->form(fn (Form $form): Form => $this->form($form->columns(2)))
            ->color('gray')
            ->slideOver()
            ->modalWidth('7xl');

    }

    protected function configureHeadingViewAction(ViewAction $action): void
    {
        $resource = static::getResource();

        if ($parent = $this->getParentRecord()) {

            $action
                ->authorize($resource::canView($parent))
                ->hidden($resource::canEdit($parent))
                ->record($parent);

            if ($url = FilamentResourceHelper::attemptToGetUrl($resource, ['view'], ['record' => $parent], false)) {
                $action->url(fn (): string => $url);
            }
        } elseif (! $resource::hasPage('view')) {
            $action->hidden();
        }

        $action
            ->infolist(fn (Infolist $infolist): Infolist => $this->infolist($infolist->columns(2)))
            ->form(fn (Form $form): Form => $this->form($form->columns(2)))
            ->model($this->getModel())
            ->modelLabel($this->getModelLabel() ?? static::getResource()::getModelLabel())
            ->color('gray')
            ->slideOver()
            ->modalWidth('7xl');

    }

    protected function configureDeleteAction(DeleteAction $action): void
    {
        parent::configureDeleteAction($action);

        $resource = static::getResource();

        $action
            ->successRedirectUrl($resource::getUrl('index', ['parent' => $this->parent]));
    }

    protected function configureDeleteBulkAction(DeleteBulkAction $action): void
    {
        parent::configureDeleteBulkAction($action);

        $resource = static::getResource();

        $action
            ->successRedirectUrl($resource::getUrl('index', ['parent' => $this->parent]));
    }
}
