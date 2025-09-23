<?php

namespace SolutionForest\InspireCms\Livewire;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Renderless;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Helpers\TreeNodeActionHelper;
use SolutionForest\InspireCms\Support\TreeNode\Concerns\CanCacheRecords;
use SolutionForest\InspireCms\Support\TreeNode\Livewire\SortableTreeComponent;

class NavigationTree extends SortableTreeComponent
{
    use CanCacheRecords {
        getModel as protected getBaseModel;
        getElquentQuery as protected getBaseElquentQuery;
    }

    protected static bool $showToolbarActions = true;

    protected static bool $searchable = true;

    protected static bool $allowDragDrop = true;

    public string $category;

    #[Reactive]
    public ?string $activeLocale = null;

    protected $listeners = [
        'refreshAllTree' => 'refreshNodes',
    ];

    protected function getNodeItemActions(): array
    {
        return [
            EditAction::make()
                ->iconButton()
                ->icon(fn (EditAction $action) => $action->getGroupedIcon() ?? Heroicon::Pencil),

            ViewAction::make()
                ->iconButton()
                ->icon(fn (ViewAction $action) => $action->getGroupedIcon() ?? Heroicon::Eye),

            DeleteAction::make()
                ->iconButton()
                ->icon(fn (DeleteAction $action) => $action->getGroupedIcon() ?? Heroicon::Trash)
                ->after(function () {
                    $this->refreshNodes();
                }),
        ];
    }

    public function saveOrder()
    {
        $data = collect($this->nodes)->map(fn ($node) => $this->transformNodeIntoRecord($node))->toArray();

        $this->getElquentQuery()->rebuildTree($data);

        Notification::make()
            ->title('Tree Updated')
            ->body('The navigation tree has been updated successfully.')
            ->success()
            ->send();

        $this->refreshNodes();
    }

    public function refreshNodes()
    {
        // Implement the logic to refresh the nodes, e.g., fetch from database
        // $this->nodes = ...;
        $records = $this->getElquentQuery()->get()->toTree();

        $this->nodes = collect($records)->map(fn ($record) => $this->transformRecordIntoNode($record))->toArray();
    }

    // region Action Configurations

    public function getDefaultActionSchemaResolver(Action $action): ?Closure
    {
        return match (true) {
            $action instanceof EditAction,
            $action instanceof CreateAction,
            $action instanceof ViewAction => fn ($schema) => $this->getResource()::form($schema),
            default => null,
        };
    }

    public function getDefaultActionAuthorizationResponse(Action $action): ?Response
    {
        return match (true) {
            $action instanceof CreateAction => $this->getResource()::getCreateAuthorizationResponse(),
            $action instanceof DeleteAction => $this->getResource()::getDeleteAuthorizationResponse($action->getRecord()),
            $action instanceof EditAction => $this->getResource()::getEditAuthorizationResponse($action->getRecord()),
            $action instanceof ViewAction => $this->getResource()::getViewAuthorizationResponse($action->getRecord()),
            default => null,
        };
    }

    /**
     * @return ?class-string<Model>
     */
    public function getDefaultActionModel(Action $action): ?string
    {
        return $this->getModel();
    }

    public function getDefaultActionModelLabel(Action $action): ?string
    {
        return $this->getResource()::getModelLabel();
    }

    public function getDefaultActionUrl(Action $action): ?string
    {
        $resourcePageParams = [
            'category' => $this->category,
            'activeLocale' => $this->activeLocale,
        ];

        if (
            ($action instanceof CreateAction) &&
            ($this->getResource()::hasPage('create'))
        ) {
            return FilamentResourceHelper::attemptToGetUrl($this->getResource(), 'create', $resourcePageParams, false);
        }

        if (
            ($action instanceof EditAction) &&
            ($this->getResource()::hasPage('edit'))
        ) {
            $resourcePageParams['record'] = $action->getRecord();

            return FilamentResourceHelper::attemptToGetUrl($this->getResource(), 'edit', $resourcePageParams, false);
        }

        if (
            ($action instanceof ViewAction) &&
            ($this->getResource()::hasPage('view'))
        ) {
            $resourcePageParams['record'] = $action->getRecord();

            return FilamentResourceHelper::attemptToGetUrl($this->getResource(), 'view', $resourcePageParams, false);
        }

        return null;
    }

    protected function resolveTreeNodeAction(array $action, array $parentActions): ?Action
    {
        $resolvedAction = $this->resolveBaseAction($action, $parentActions);

        $recordKey = $action['context']['recordKey'] ?? $action['arguments']['node']['id'] ?? null;

        if ($recordKey && $resolvedAction && is_null($resolvedAction->getRecord())) {

            $record = $this->retrieveRecordById($recordKey);

            $resolvedAction->getRootGroup()?->record($record) ?? $resolvedAction->record($record);

        }

        return $resolvedAction;
    }

    // endregion Action Configurations

    /**
     * @param  Model  $record
     * @return array
     */
    protected function transformRecordIntoNode($record)
    {
        $actions = static::$showNodeActions ? $this->getNodeItemActions() : [];

        $node = [
            'id' => $record->getKey(),
            'name' => str($record->hasTranslation('title', $this->activeLocale) ? $record->getTranslation('title', $this->activeLocale) : $record->title)
                ->when(! $record->isVisibility(), fn ($str) => str($str)->append(' (Hidden)'))
                ->toString(),
            'description' => ($url = $record->getUrl($this->activeLocale)) && filled($url) ? $url : null,
            'children' => collect($record->children)->map(fn ($child) => $this->transformRecordIntoNode($child))->toArray(),
        ];

        $visibleActions = collect($actions)
            ->flatMap(function ($action) use ($record) {
                if ($action instanceof Action) {
                    return [$action->record($record)];
                } elseif ($action instanceof ActionGroup) {
                    return collect($action->getFlatActions())
                        ->map(fn (Action $subAction) => $subAction->record($record))
                        ->all();
                }

                return [];
            })
            ->whereInstanceOf(Action::class)
            ->map(fn (Action $action) => $action->arguments(['node' => $node]))
            ->where(fn (Action $action) => $action->isVisible())
            ->map(fn (Action $action) => $action->getName())
            ->values()
            ->all();

        $node['__visibleActions'] = $visibleActions;

        return $node;
    }

    protected function transformNodeIntoRecord(array $node)
    {
        return [
            'id' => $node['id'] ?? throw new \Exception('ID is missing in node'),
            'title' => $node['name'] ?? $node['id'] ?? throw new \Exception('Name is missing in node'),
            'children' => collect($node['children'] ?? [])->map(fn ($child) => $this->transformNodeIntoRecord($child))->toArray(),
        ];
    }

    #[Renderless]
    public function getNodeItemActionsHtml($id)
    {
        $node = $this->getNodeById($id);

        $actions = $this->getNodeItemActions();

        if ($node) {
            $actions = TreeNodeActionHelper::getNodeActions(
                node: $node,
                livewireActions: $actions,
                model: $this->getModel(),
                livewire: $this,
                resolveRecordUsing: function ($arguments, $key) {
                    if ($key instanceof Model) {
                        return $key;
                    }

                    $recordKey = $arguments['nodeId'] ?? $key ?? null;

                    if (is_null($recordKey) || empty($recordKey)) {
                        return null;
                    }

                    return $this->retrieveRecordById($recordKey);
                },
            );
        }

        return collect($actions)
            ->map(fn (Action | ActionGroup $action) => $action->toHtml())
            ->all();
    }

    protected function getElquentQuery(): Builder
    {
        return $this->getModel()::scoped(['category' => $this->category])
            ->withDepth()
            ->with([
                'content' => fn ($q) => $q->withTrashed(),
                'children',
            ])
            ->defaultOrder();
    }

    protected function getModel(): string
    {
        return InspireCmsConfig::getNavigationModelClass();
    }

    /**
     * @return class-string<\Filament\Resources\Resource>
     */
    protected function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation', NavigationResource::class);
    }

    protected function getNodeById($id)
    {
        foreach ($this->nodes as $node) {
            if ($node['id'] == $id) {
                return $node;
            }

            if (! empty($node['children'])) {
                $childNode = $this->getNodeByIdRecursive($node['children'], $id);
                if ($childNode) {
                    return $childNode;
                }
            }
        }

        return null;
    }

    protected function getNodeByIdRecursive($nodes, $id)
    {
        foreach ($nodes as $node) {
            if ($node['id'] == $id) {
                return $node;
            }

            if (! empty($node['children'])) {
                $childNode = $this->getNodeByIdRecursive($node['children'], $id);
                if ($childNode) {
                    return $childNode;
                }
            }
        }

        return null;
    }
}
