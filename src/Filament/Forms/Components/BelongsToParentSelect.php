<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;

class BelongsToParentSelect extends Select
{
    protected int | string | Closure $rootParentId = 0;

    public function nestableParentRelationship(string | Closure | null $name = null, string | Closure | null $titleAttribute = null, bool $ignoreRecord = false, ?string $emptyStateLabel = null): static
    {
        $modifyQueryUsing = function ($query, $record) {

            // Skip its children
            if ($record && $record instanceof HasRecursiveRelationshipsInterface) {
                // Exclude the current record and all its descendants
                $descendantIds = $record->descendants()->map(fn ($item) => $item->getKey())->toArray();
                $query->whereKeyNotIn($descendantIds);
            }

            return $query;
        };

        if (empty($emptyStateLabel)) {
            $emptyStateLabel = '(' . strtolower(__('inspirecms::inspirecms.no_parent') . ')');
        }
        $this->placeholder($emptyStateLabel);

        $this->native(false);

        $this->relationship(name: $name, titleAttribute: $titleAttribute, ignoreRecord: $ignoreRecord, modifyQueryUsing: $modifyQueryUsing);

        $baseLoadStateFromRelationshipsUsing = clone $this->loadStateFromRelationshipsUsing;

        $this->loadStateFromRelationshipsUsing(static function (BelongsToParentSelect $component, $state) use ($baseLoadStateFromRelationshipsUsing): void {
            if (filled($state)) {
                if ($state == 0 || $state == $component->getRootParentId()) {
                    // If no parent ID == "0" (root level)
                    $component->state(null);
                }

                return;
            }

            $component->evaluate($baseLoadStateFromRelationshipsUsing);
        });

        // Dehydrated ParentId for "No Parent"
        $this->dehydrateStateUsing(function (BelongsToParentSelect $component, $state) {
            if (empty($state)) {
                return $component->getRootParentId();
            }

            return $state;
        });

        return $this;
    }

    public function rootParentId(int | string | Closure $rootParentId): static
    {
        $this->rootParentId = $rootParentId;

        return $this;
    }

    public function getRootParentId(): string | int
    {
        return $this->evaluate($this->rootParentId);
    }
}
