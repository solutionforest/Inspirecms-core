<?php

namespace SolutionForest\InspireCms\Livewire;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Renderless;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Helpers\ContentHelper;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\Helpers\TreeNodeActionHelper;
use SolutionForest\InspireCms\Support\TreeNode\Concerns\CanCacheRecords;
use SolutionForest\InspireCms\Support\TreeNode\Livewire\ServerSideTreeComponent;

class BaseContentTreeNode extends ServerSideTreeComponent
{
    use CanCacheRecords {
        getModel as protected getBaseModel;
        getElquentQuery as protected getBaseElquentQuery;
    }

    protected static bool $showNodeActions = false;

    protected static bool $enableSelection = true;

    protected static bool $skipChildrenIfTableView = true;

    public ?string $activeLocale = null;

    public array $redirectUrlParameters = []; // Additional URL parameters for resource links

    public bool $filterByPermission = true;

    public function mount()
    {
        parent::mount();

        if (! isset($this->activeLocale)) { // set default locale if not set
            $this->activeLocale = collect(InspireCms::getAllAvailableLanguages())->keys()->first();
        }
    }

    protected function getRootNodes(): array
    {
        // Return array of root nodes
        $records = $this->fetchChildNodes($this->startNodeId);

        $records->each(fn ($record) => $this->cacheRecordAppend($record));

        $nodes = $records
            ->map(fn ($record) => $this->transformRecordIntoNode($record))
            ->toArray();

        return $nodes;
    }

    protected function getChildNodes(string $parentId): array
    {
        // Return array of child nodes for the given parent
        $records = $this->fetchChildNodes($parentId);

        $records->each(fn ($record) => $this->cacheRecordAppend($record));

        $node = $records
            ->map(fn ($record) => $this->transformRecordIntoNode($record))
            ->toArray();

        return $node;
    }

    /**
     * @param  Model  $record
     * @return array
     */
    protected function transformRecordIntoNode($record)
    {
        // Each node should have: id, name, icon, has_children, parent_id, depth
        if ($this->activeLocale) {
            $record->setLocale($this->activeLocale);
        }

        $depth = (count($record->ancestorsAndSelf ?? [])) - 1;

        $translatable = [
            'title' => $record->getTranslations('title'),
        ];

        $resourcePage = null;
        $url = null;

        // authorize user to view/edit the record
        if (($resource = $this->getFilamentResource()) &&
            ($resourcePage = FilamentResourceHelper::retrieveFirstAccessiblePage($resource, ['edit', 'view'], ['record' => $record])) &&
            (is_array($resourcePage) || is_string($resourcePage))
        ) {
            $url = FilamentResourceHelper::attemptToGetUrl(
                $resource,
                $resourcePage,
                [
                    'record' => $record->getKey(),
                    ...$this->redirectUrlParameters,
                    'locale' => $this->activeLocale,
                ],
                false
            );
        }

        $hasChildren = $record->children_count > 0;

        if (static::$skipChildrenIfTableView
            && ($documentType = $record?->documentType)
            && $documentType->show_as_table === true
        ) {
            $hasChildren = false;
        }

        $node = [
            'id' => $record->getKey(),
            'name' => $record->title,
            'icon' => $record->documentType?->icon,
            'has_children' => $hasChildren,
            'parent_id' => $record->getParentId(),
            'depth' => $depth,

            // actions config
            '__content_document_type_id' => $record?->document_type_id,
            '__content_document_type_cat' => $record?->documentType?->category,
            '__content_tree_id' => $record?->nestableTree?->getKey(),

            '__content_is_default' => $record->is_default === true,

            'url' => $url,
            '__fi_resource_page' => $resourcePage,

            'translatable' => $translatable,
        ];

        $visibleActions = collect($this->getNodeItemActions())
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
            ->map(fn (Action $action) => $action(['node' => $node]))
            ->where(fn (Action $action) => $action->isVisible())
            ->map(fn (Action $action) => $action->getName())
            ->values()
            ->all();

        $node['__visibleActions'] = $visibleActions;

        return $node;
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

    #[Renderless]
    public function getNodeItemActionsHtml($id)
    {
        $node = $this->getNodeById($id);

        $actions = $this->getNodeItemActions();

        if ($node) {

            $actions = TreeNodeActionHelper::getNodeActions(
                node: collect($node)
                    ->except(['__fi_resource_page', 'url'])
                    ->all(),
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

    public function getNodeLabel($node): array
    {
        $currentLocale = $this->activeLocale;
        $fallbackLocale = InspireCms::getFallbackLanguage()?->code ?? app()->getLocale();
        $currentLocaleTitle = $node['translatable']['title'][$currentLocale] ?? $node['translatable']['title'][$fallbackLocale] ?? null;

        return [
            'title' => $currentLocaleTitle ?? $node['title'] ?? 'Untitled',
            'description' => null,
        ];
    }

    public function getNodeUrl(array $node): ?string
    {
        if (($resourcePage = $node['__fi_resource_page'] ?? null) && ($resource = $this->getFilamentResource())) {
            return FilamentResourceHelper::attemptToGetUrl(
                $resource,
                $resourcePage,
                [
                    'record' => $node['id'] ?? null,
                    ...$this->redirectUrlParameters,
                    'locale' => $this->activeLocale,
                ],
                false
            );
        }

        return $node['url'] ?? null;
    }

    /**
     * @return class-string<Model>
     */
    protected function getModel()
    {
        return InspireCmsConfig::getContentModelClass();
    }

    protected function getModelRootLevelParentId(): int | string
    {
        return app(static::getModel())->getRootLevelParentId();
    }

    /**
     * @return class-string<\Filament\Resources\Resource> | null
     */
    protected function getFilamentResource()
    {
        return InspireCmsConfig::getFilamentResource('content', ContentResource::class);
    }

    /**
     * @param  mixed  $parentId
     * @return Collection<int, Model|Content>
     */
    protected function fetchChildNodes($parentId = null)
    {
        $query = $this->getElquentQuery();
        if ($parentId) {
            $query->whereParent($parentId);
        } else {
            $query->whereIsRoot();
        }

        return $query->get();
    }

    protected function getElquentQuery(): Builder
    {
        $model = $this->getModel();

        $query = $model::query()
            ->with([
                'documentType',
            ])
            ->withCount('children')
            // calculate depth
            ->with('ancestorsAndSelf');

        if ($this->filterByPermission) {

            $idsOrBool = ContentHelper::getAccessibleContentIds();

            // Can access all
            if ($idsOrBool === true) {
                return $query;
            }

            $ids = is_array($idsOrBool) ? collect($idsOrBool)->unique()->filter()->where(fn ($item) => is_string($item) && filled($item))->unique()->values()->all() : [];
            $modelKey = app($model)->getKeyName();

            if (! empty($ids)) {

                $query->where(function (Builder $q) use ($ids, $modelKey) {
                    $q->whereIn($modelKey, $ids)
                        ->orWhereIn('parent_id', $ids);
                });
            }
        }

        return $query;
    }

    // Navigation methods implementation
    protected function getIndexUrl(): string
    {
        // Return URL to content resource index
        if ($resource = $this->getFilamentResource()) {
            $indexUrl = FilamentResourceHelper::attemptToGetUrl(
                $resource,
                'index',
                [
                    'locale' => $this->activeLocale,
                    ...$this->redirectUrlParameters,
                ],
                false
            );

            if ($indexUrl) {
                return $indexUrl;
            }
        }

        // Fallback to root
        return '/';
    }

    protected function getNodeById(string $nodeId): ?array
    {
        // Search in visible nodes first
        foreach ($this->visibleNodes as $node) {
            if ($node['id'] === $nodeId) {
                return $node;
            }
        }

        // If not found, search in root nodes
        foreach ($this->nodes as $node) {
            if ($node['id'] === $nodeId) {
                return $node;
            }
        }

        // If still not found, search in cached children
        foreach ($this->loadedChildrenCache as $children) {
            foreach ($children as $node) {
                if ($node['id'] === $nodeId) {
                    return $node;
                }
            }
        }

        return null;
    }
}
