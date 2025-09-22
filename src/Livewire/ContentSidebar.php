<?php

namespace SolutionForest\InspireCms\Livewire;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\SelectAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\AdjustChildOrderAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\CreateContentAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\MoveContentAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\SetDefaultContentPageAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\UpdateContentRouteAction;

class ContentSidebar extends BaseContentTreeNode
{
    protected static bool $showNodeActions = true;

    protected static bool $enableNodeUrls = true;

    protected $listeners = [
        'updatedActiveLocale' => '$refresh',
    ];

    protected function queryString()
    {
        return [
            'activeLocale' => ['except' => null],
        ];
    }

    protected function getToolbarActions(): array
    {
        return [
            SelectAction::make('activeLocale')
                ->options(
                    collect(InspireCms::getAllAvailableLanguages())
                        ->map(fn (LanguageDto $lang) => $lang->getLabel())
                        ->all()
                ),
        ];
    }

    protected function getNavigationHeaderActions(): array
    {
        return [

            ActionGroup::make([

                CreateContentAction::make()
                    ->color('primary'),

                AdjustChildOrderAction::make()
                    ->after(fn () => $this->refreshTree()),

            ])->dropdownPlacement('right-top'),

        ];
    }

    protected function getNodeItemActions(): array
    {
        return [

            ActionGroup::make([

                CreateContentAction::make()
                    ->color('primary')
                    ->name('createContentFromNode')
                    ->parentContentKey(fn (array $arguments) => $arguments['node']['id'] ?? null)
                    ->parentDocumentType(fn (array $arguments) => $arguments['node']['__content_document_type_id'] ?? null)
                    ->nodeTitleUsing(fn (array $arguments) => $arguments['node']['name'] ?? null),

                AdjustChildOrderAction::make()
                    ->name('reorderContentChildrenFromNode')
                    ->nodeParentId(fn (array $arguments) => $arguments['node']['__content_tree_id'] ?? null)
                    ->after(fn () => $this->refreshTree()),

                ActionGroup::make([

                    SetDefaultContentPageAction::make(),

                    UpdateContentRouteAction::make(),

                    ActionGroup::make([

                        MoveContentAction::make()
                            ->name('moveContentToUnderRoot')
                            ->moveUnderRoot(true)
                            ->after(fn () => $this->refreshTree()),

                        MoveContentAction::make()
                            ->moveUnderRoot(false)
                            ->after(fn () => $this->refreshTree()),

                    ])
                        ->color('gray')
                        ->iconPosition(IconPosition::After)
                        ->icon(Heroicon::ArrowRight)
                        ->label(__('inspirecms::buttons.move_to.label')),

                    DeleteAction::make()
                        ->after(fn () => $this->refreshTree()),

                ])->dropdown(false),

            ])->dropdownPlacement('right-top'),
        ];
    }

    // public function getNodeItemActionsByNodes(array $nodes): array
    // {
    //     if (!$this->showNodeActions) {
    //         return [];
    //     }

    //     // Step 1: Generate cache key from node IDs
    //     $cacheKey = $this->generateNodesCacheKey($nodes);

    //     // Step 2: Check if actions are already cached
    //     if (isset($this->nodeActionsCache[$cacheKey])) {
    //         return $this->nodeActionsCache[$cacheKey];
    //     }

    //     // Step 3: Get base actions template
    //     $baseActions = $this->getNodeItemActions();
    //     if (empty($baseActions)) {
    //         return [];
    //     }

    //     // Step 4: Fetch records and apply to actions
    //     $formattedActions = $this->formatActionsWithRecords($baseActions, $nodes);

    //     // Step 5: Cache the formatted actions
    //     $this->nodeActionsCache[$cacheKey] = $formattedActions;

    //     return $formattedActions;
    // }

    // protected function generateNodesCacheKey(array $nodes): string
    // {
    //     $nodeIds = array_column($nodes, 'id');
    //     sort($nodeIds); // Ensure consistent ordering
    //     return md5(implode('|', $nodeIds));
    // }

    // /**
    //  * @param array<Action|ActionGroup> $baseActions
    //  * @param array $nodes
    //  * @return array<array>
    //  */
    // protected function formatActionsWithRecords(array $baseActions, array $nodes): array
    // {
    //     $formattedActions = [];

    //     $this->nodeRecordsCache = array_merge(
    //         $this->nodeRecordsCache,
    //         $this->getElquentQuery()
    //             ->findMany(
    //                 collect($nodes)
    //                     ->pluck('id')
    //                     ->filter()
    //                     ->filter(fn ($id) => !isset($this->nodeRecordsCache[$id]))
    //                     ->values()
    //                     ->all()
    //             )
    //             ->keyBy(fn ($record) => $record->getKey())
    //             ->all()
    //     );

    //     foreach ($baseActions as $index => $action) {
    //         $formattedActions[$index] = [];

    //         foreach ($nodes as $node) {
    //             $nodeId = $node['id'];

    //             // Get or fetch record for this node
    //             $record = $this->nodeRecordsCache[$nodeId] ?? null;

    //             $formattedActions[$index] = $action->applyTreeNodeRecord($record, $node);
    //         }
    //     }

    //     return $formattedActions;
    // }

    // /**
    //  * Clear all caches - useful when data changes
    //  */
    // public function clearActionCaches(): void
    // {
    //     $this->nodeActionsCache = [];
    //     $this->nodeRecordsCache = [];
    // }

    // /**
    //  * Clear cache for specific nodes
    //  */
    // public function clearNodeActionCache(array $nodeIds): void
    // {
    //     // Clear record cache for specific nodes
    //     foreach ($nodeIds as $nodeId) {
    //         unset($this->nodeRecordsCache[$nodeId]);
    //     }

    //     // Clear action caches that might contain these nodes
    //     // This is a simple approach - could be optimized further
    //     $this->nodeActionsCache = [];
    // }

    // public function refreshTree(): void
    // {
    //     $this->clearActionCaches(); // Clear action caches when refreshing
    //     parent::refreshTree();
    // }

    protected function getElquentQuery(): Builder
    {
        return parent::getElquentQuery()
            ->with([
                'documentType', // icon and pre-check for UpdateContentRouteAction

                'nestableTree', // use for sorting action
                'locked', // pre-check for Record action, UpdateContentRouteAction
            ])
            // sort by tree id for display consistency
            ->sortedByTree();
    }

    public function render()
    {
        return view('inspirecms::livewire.content-sidebar', $this->viewData());
    }
}
