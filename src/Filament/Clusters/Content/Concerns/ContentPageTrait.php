<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCms\Filament\Actions\CreateContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentCreatePage;
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
                    ->record(fn (array $arguments) => $this->resolveSelectedModelItem($arguments['key']))
                    ->form(function ($record) use ($modelClass) {
                        $parentQuery = $modelClass::query();
                        if ($record) {
                            $parentQuery = $parentQuery
                                ->whereKeyNot($record->getKey())
                                ->whereKeyNot($record->parent_id);
                        }

                        return [
                            Forms\Components\Toggle::make('asRoot')
                                ->live(),
                            PaginationPicker::make('parent')
                                ->paginationOptions($parentQuery)
                                ->recordTitleUsing(fn ($record) => $record->title)
                                ->tableColumns([
                                    Tables\Columns\TextColumn::make('id'),
                                    Tables\Columns\TextColumn::make('title'),
                                    Tables\Columns\TextColumn::make('slug'),
                                ])
                                ->maxItems(1)
                                ->minItems(1)
                                ->visible(fn ($get) => $get('asRoot') === false),
                        ];
                    })
                    ->action(function (?Model $record, array $data, $action) use ($rootLevelKey) {
                        if ($record) {
                            if (isset($data['asRoot']) && $data['asRoot'] === true) {

                                $record->parent_id = $rootLevelKey;

                            } elseif (isset($data['parent'])) {

                                $record->parent_id = $data['parent'][0];
                            }

                            $record->save();

                            $action->success();
                        }
                    })
                    ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'index', [], false))
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

    public function getSubNavigation(): array
    {
        return [];
    }
}
