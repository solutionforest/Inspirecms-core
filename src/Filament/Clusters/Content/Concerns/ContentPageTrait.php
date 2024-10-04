<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCms\FieldTypes\Configs\ContentPicker;
use SolutionForest\InspireCms\Filament\Actions\CreateContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentEditPage;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentViewPage;
use SolutionForest\InspireCms\Filament\Forms\Components\PaginationPicker;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\TreeNodes\Concerns\InteractsWithModelExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

trait ContentPageTrait
{
    use InteractsWithModelExplorer {
        modelExplorer as protected traitModelExplorer;
    }

    public array $expandedModelExplorerItems = [];

    public function mountContentPageTrait()
    {
        if (! $this instanceof ListRecords) {
            $this->refreshModelExplorerSidebar();
        }
    }

    public function modelExplorer(ModelExplorer $modelExplorer): ModelExplorer
    {
        $modelClass = $this->getModel();
        $model = new $modelClass;
        $parentIdColumn = $model->getNestableParentIdColumn();
        $rootLevelKey = $model->getNestableRootValue();

        return $modelExplorer
            ->model($modelClass)
            ->parentColumnName($parentIdColumn)
            ->rootLevelKey($rootLevelKey)
            ->modifyQueryUsing(fn ($query) => $query->withCount('children'))
            ->determineRecordLabelUsing(fn ($record) => $record->title)
            ->determineRecordHasChildrenUsing(fn ($record) => $record->children_count > 0)
            ->mutuateRootNodeItemsUsing(fn ($items) => array_merge([
                [
                    'key' => 'root',
                    'parentKey' => $this->getModelExplorer()->getRootLevelKey(),
                    'label' => __('inspirecms::inspirecms.root'),
                    'hasChildren' => false,
                    'depth' => 0,
                    'icon' => 'heroicon-o-home',
                    'link' => FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['index'], [], false),
                ],
            ], $items))
            ->mutuateNodeItemsUsing(fn (array $item, Model $record) => array_merge($item, [
                'link' => FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['edit', 'view'], ['record' => $record->getKey()], false),
            ]))
            ->actions([
                CreateContentAction::make(),
                Actions\Action::make('linkToParent')
                    // ->record(fn (array $arguments) => $this->resolveSelectedModelItem($arguments['key']))
                    ->form([
                        PaginationPicker::make('parent')
                            ->paginationOptions($modelClass::query())
                    ])
                    ->successNotificationTitle('not implemented')
                    ->hidden(fn (array $arguments) => $arguments['key'] === 'root'),
                Actions\Action::make('reorder')
                    ->modalContent(new HtmlString('not implemented'))
                    ->hidden(fn (array $arguments) => $arguments['key'] === 'root'),
            ]);
    }

    protected function resolveSelectedModelItem(string | int $key): ?Model
    {
        if (in_array($key, ['root'])) {
            return null;
        }
        return $this->getModelExplorer()->findRecord($key);
    }

    protected function configureSelectedModelItemFormAction(Actions\Action $action): void
    {
        match (true) {
            $action instanceof CreateContentAction => $action
                ->color('gray')
                ->extraAttributes(['class' => 'flex-1'])
                ->modifyUrlParameterUsing(function (array $arguments, array $parameters) {
                    $parent = $arguments['key'] ?? null;
                    if (in_array($parent, ['root'])) {
                        $parent = null;
                    }
                    return array_merge($parameters, [
                        'parent' => $parent,
                    ]);
                }),
            default => null,
        };

        $this->cacheAction($action);
    }

    protected function refreshModelExplorerSidebar(): void
    {
        $record = $this instanceof BaseContentCreatePage ?
            $this->getParentRecord() :
            $this->getRecord();

        if (! $record) {
            return;
        }

        if ($record->trashed()) {
            $this->expandedModelExplorerItems = [];

            return;
        }

        $this->selectedModelItem($record);

        $ancestors = collect($record->ancestors())->push($record);
        foreach ($ancestors as $index => $ancestor) {
            $this->getModelExplorerNodes($ancestor->parent_id, $index);
            if ($ancestor->getKey() !== $record->getKey()) {
                $this->expandedModelExplorerItems[] = $ancestor->getKey();
            }
        }
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $originalBreadcrumbs = parent::getBreadcrumbs();

        if ($this instanceof BaseContentCreatePage) {
            $parent = $this->getParentRecord();

            $breadcrumbs = array_slice($originalBreadcrumbs, 0, -1);
            $slicedBreadcrumbs = array_slice($originalBreadcrumbs, -1);

        } elseif ($this instanceof BaseContentEditPage || $this instanceof BaseContentViewPage) {
            $parent = $this->getParent();

            $breadcrumbs = array_slice($originalBreadcrumbs, 0, -2);
            $slicedBreadcrumbs = array_slice($originalBreadcrumbs, -2);

        } else {
            $parent = null;

            $breadcrumbs = $originalBreadcrumbs;
            $slicedBreadcrumbs = [];
        }

        $ancestors = $parent ? collect($parent->ancestors())->push($parent)->filter() : collect();

        foreach ($ancestors as $ancestor) {
            $parameters = ['record' => $ancestor];
            $url = FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view'], $parameters, true);

            $parentTitle = $resource::getRecordTitle($ancestor) ?? $ancestor->getKey();

            if ($url) {
                $breadcrumbs[$url] = $parentTitle;
            } else {
                $breadcrumbs[] = $parentTitle;
            }
        }

        $breadcrumbs = array_merge($breadcrumbs, $slicedBreadcrumbs);

        return $breadcrumbs;
    }

    public function getSubNavigation(): array
    {
        return [];
    }
}
