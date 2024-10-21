<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCms\Filament\Actions\CreateContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages\BaseContentCreatePage;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\TreeNodes\Concerns\InteractsWithModelExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer;

trait ContentPageTrait
{
    use InteractsWithModelExplorer {
        modelExplorer as protected traitModelExplorer;
        setSelectedModelItem as protected traitSetSelectedModelItem;
        getModelExplorerItemsFrom as protected traitGetModelExplorerItemsFrom;
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
            ->modifyQueryUsing(fn ($query) => $query
                ->withCount([
                    'children',
                ])
                ->with([
                    'documentType',
                ])
            )
            ->determineRecordLabelUsing(fn ($record) => $record->title)
            ->determineRecordHasChildrenUsing(function ($record) {
                if ($record->documentType->isShowChildrenAsTable()) {
                    return false;
                }
                return $record->children_count > 0;
            })
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
            ->mutuateNodeItemsUsing(function (array $item, Model $record) {
                $item['link'] = FilamentResourceHelper::attemptToGetUrl(static::getResource(), ['edit', 'view'], ['record' => $record], true);
                return $item;
            })
            ->actions([
                CreateContentAction::make(),
                Actions\Action::make('linkToParent')
                    ->record(fn (array $arguments) => $this->resolveSelectedModelItem($arguments['key']))
                    ->form(function ($record) {
                        return [
                            Forms\Components\Toggle::make('asRoot')
                                ->live(),
                            ContentPicker::make('parent')
                                ->exceptRecord(fn () => [$record, $record->parent_id])
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
                Actions\Action::make('delete_item')
                    ->color('danger')
                    ->icon(FilamentIcon::resolve('actions::delete-action.grouped') ?? 'heroicon-m-trash')
                    ->requiresConfirmation()
                    ->record(fn (array $arguments) => $this->resolveSelectedModelItem($arguments['key']))
                    ->hidden(fn (array $arguments) => $arguments['key'] === 'root')
                    ->successRedirectUrl(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'index', [], false))
                    ->action(function (?Model $record, Actions\Action $action) {

                        if (! $record) {
            
                            return;
                        }

                        $result = $record->delete();

                        if (! $result) {
                            $action->failure();
            
                            return;
                        }
            
                        $action->success();
                    }),
            ]);
    }

    //region Model Explorer
    protected function getModelExplorerItemsFrom(string | int $parentKey, int $depth): array
    {
        $selectItem = $this->resolveSelectedModelItem($parentKey);

        if ($selectItem?->documentType->isShowChildrenAsTable()) {
            return [];
        }

        return $this->traitGetModelExplorerItemsFrom($parentKey, $depth);
    }

    protected function setSelectedModelItem(string | int | Model | null $record): void
    {
        if ($record) {

            $item = $record instanceof Model ? $record : $this->resolveSelectedModelItem($record);

            if ($item->parent?->documentType->isShowChildrenAsTable()) {
                $this->traitSetSelectedModelItem($item->parent);
                return;
            }
        }

        $this->traitSetSelectedModelItem($item);
        return;
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
    //endregion

    public function getSubNavigation(): array
    {
        return [];
    }
}
