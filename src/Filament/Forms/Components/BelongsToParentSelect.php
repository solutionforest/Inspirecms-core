<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Znck\Eloquent\Relations\BelongsToThrough;

class BelongsToParentSelect extends Select
{
    protected $rootParentId = 0;

    public function nestableParentRelationship(string | Closure | null $name = null, string | Closure | null $titleAttribute = null, bool $ignoreRecord = false, ?string $emptyStateLabel = null): static
    {
        $modifyQueryUsing = function ($query, $record) {

            // Skip its children
            if ($record) {
                // Exclude the current record and all its descendants
                $descendantIds = $record->descendants()->pluck('id')->toArray();
                $query->whereNotIn('id', $descendantIds);
            }

            return $query;
        };

        if (empty($emptyStateLabel)) {
            $emptyStateLabel = '(' . strtolower(__('inspirecms::inspirecms.no_parent') . ')');
        }
        $this->placeholder($emptyStateLabel);

        $this->native(false);

        $this->relationship(name: $name, titleAttribute: $titleAttribute, ignoreRecord: $ignoreRecord, modifyQueryUsing: $modifyQueryUsing);

        $this->loadStateFromRelationshipsUsing(static function (BelongsToParentSelect $component, $state) use ($modifyQueryUsing): void {
            if (filled($state)) {
                if ($state == 0 && $state == $component->getRootParentId()) {
                    // If no parent ID == "0" (root level)
                    $component->state(null);
                }

                return;
            }

            $relationship = $component->getRelationship();

            if (
                ($relationship instanceof BelongsToMany) ||
                ($relationship instanceof HasManyThrough)
            ) {
                if ($modifyQueryUsing) {
                    $component->evaluate($modifyQueryUsing, [
                        'query' => $relationship->getQuery(),
                        'search' => null,
                    ]);
                }

                /** @var Collection $relatedRecords */
                $relatedRecords = $relationship->getResults();

                $component->state(
                    // Cast the related keys to a string, otherwise JavaScript does not
                    // know how to handle deselection.
                    //
                    // https://github.com/filamentphp/filament/issues/1111
                    $relatedRecords
                        ->pluck(($relationship instanceof BelongsToMany) ? $relationship->getRelatedKeyName() : $relationship->getRelated()->getKeyName())
                        ->map(static fn ($key): string => strval($key))
                        ->all(),
                );

                return;
            }

            if ($relationship instanceof BelongsToThrough) {
                /** @var ?Model $relatedModel */
                $relatedModel = $relationship->getResults();

                $component->state(
                    $relatedModel?->getAttribute(
                        $relationship->getRelated()->getKeyName(),
                    ),
                );

                return;
            }

            if ($relationship instanceof HasMany) {
                /** @var Collection $relatedRecords */
                $relatedRecords = $relationship->getResults();

                $component->state(
                    $relatedRecords
                        ->pluck($relationship->getForeignKeyName())
                        ->all(),
                );

                return;
            }

            if ($relationship instanceof HasOne) {
                $relatedModel = $relationship->getResults();

                $component->state(
                    $relatedModel?->getAttribute(
                        $relationship->getForeignKeyName(),
                    ),
                );

                return;
            }

            /** @var BelongsTo $relationship */
            $relatedModel = $relationship->getResults();

            $component->state(
                $relatedModel?->getAttribute(
                    $relationship->getOwnerKeyName(),
                ),
            );
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

    public function getRootParentId()
    {
        return $this->rootParentId;
    }
}
