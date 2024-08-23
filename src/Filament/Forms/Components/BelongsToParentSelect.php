<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Support\Services\RelationshipJoiner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
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

        $this->relationship = $name ?? $this->getName();
        $this->relationshipTitleAttribute = $titleAttribute;
        // $this->relationship(name: $name, titleAttribute: $titleAttribute, ignoreRecord: $ignoreRecord, modifyQueryUsing: $modifyQueryUsing);

        $this->getSearchResultsUsing(static function (BelongsToParentSelect $component, ?string $search) use ($modifyQueryUsing, $ignoreRecord): array {
            $relationship = Relation::noConstraints(fn () => $component->getRelationship());

            $relationshipQuery = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);

            if ($ignoreRecord && ($record = $component->getRecord())) {
                $relationshipQuery->where($record->getQualifiedKeyName(), '!=', $record->getKey());
            }

            if ($modifyQueryUsing) {
                $relationshipQuery = $component->evaluate($modifyQueryUsing, [
                    'query' => $relationshipQuery,
                    'search' => $search,
                ]) ?? $relationshipQuery;
            }

            $component->applySearchConstraint(
                $relationshipQuery,
                generate_search_term_expression($search, $component->isSearchForcedCaseInsensitive(), $relationshipQuery->getConnection()),
            );

            $baseRelationshipQuery = $relationshipQuery->getQuery();

            if (isset($baseRelationshipQuery->limit)) {
                $component->optionsLimit($baseRelationshipQuery->limit);
            } else {
                $relationshipQuery->limit($component->getOptionsLimit());
            }

            $qualifiedRelatedKeyName = $component->getQualifiedRelatedKeyNameForRelationship($relationship);

            if ($component->hasOptionLabelFromRecordUsingCallback()) {
                return $relationshipQuery
                    ->get()
                    ->mapWithKeys(static fn (Model $record) => [
                        $record->{Str::afterLast($qualifiedRelatedKeyName, '.')} => $component->getOptionLabelFromRecord($record),
                    ])
                    ->toArray();
            }

            $relationshipTitleAttribute = $component->getRelationshipTitleAttribute();

            if (empty($relationshipQuery->getQuery()->orders)) {
                $relationshipQuery->orderBy($relationshipQuery->qualifyColumn($relationshipTitleAttribute));
            }

            if (str_contains($relationshipTitleAttribute, '->')) {
                if (! str_contains($relationshipTitleAttribute, ' as ')) {
                    $relationshipTitleAttribute .= " as {$relationshipTitleAttribute}";
                }
            } else {
                $relationshipTitleAttribute = $relationshipQuery->qualifyColumn($relationshipTitleAttribute);
            }

            return $relationshipQuery
                ->pluck($relationshipTitleAttribute, $qualifiedRelatedKeyName)
                ->toArray();
        });

        $this->options(static function (BelongsToParentSelect $component) use ($modifyQueryUsing, $ignoreRecord, $emptyStateLabel): ?array {
            if (($component->isSearchable()) && ! $component->isPreloaded()) {
                return null;
            }

            $relationship = Relation::noConstraints(fn () => $component->getRelationship());

            $relationshipQuery = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);

            if ($ignoreRecord && ($record = $component->getRecord())) {
                $relationshipQuery->where($record->getQualifiedKeyName(), '!=', $record->getKey());
            }

            if ($modifyQueryUsing) {
                $relationshipQuery = $component->evaluate($modifyQueryUsing, [
                    'query' => $relationshipQuery,
                    'search' => null,
                ]) ?? $relationshipQuery;
            }

            $qualifiedRelatedKeyName = $component->getQualifiedRelatedKeyNameForRelationship($relationship);

            if ($component->hasOptionLabelFromRecordUsingCallback()) {
                $originalOptions = $relationshipQuery
                    ->get()
                    ->mapWithKeys(static fn (Model $record) => [
                        $record->{Str::afterLast($qualifiedRelatedKeyName, '.')} => $component->getOptionLabelFromRecord($record),
                    ])
                    ->toArray();

                return array_merge([
                    $component->getRootParentId() => $emptyStateLabel,
                ], $originalOptions);
            }

            $relationshipTitleAttribute = $component->getRelationshipTitleAttribute();

            if (empty($relationshipQuery->getQuery()->orders)) {
                $relationshipQuery->orderBy($relationshipQuery->qualifyColumn($relationshipTitleAttribute));
            }

            if (str_contains($relationshipTitleAttribute, '->')) {
                if (! str_contains($relationshipTitleAttribute, ' as ')) {
                    $relationshipTitleAttribute .= " as {$relationshipTitleAttribute}";
                }
            } else {
                $relationshipTitleAttribute = $relationshipQuery->qualifyColumn($relationshipTitleAttribute);
            }

            $originalOptions = $relationshipQuery
                ->pluck($relationshipTitleAttribute, $qualifiedRelatedKeyName)
                ->toArray();

            return array_merge([
                $component->getRootParentId() => $emptyStateLabel,
            ], $originalOptions);
        });

        $this->loadStateFromRelationshipsUsing(static function (BelongsToParentSelect $component, $state) use ($modifyQueryUsing): void {
            if (filled($state)) {
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

        $this->getOptionLabelUsing(static function (BelongsToParentSelect $component) use ($emptyStateLabel) {
            $record = $component->getSelectedRecord();

            if (! $record) {
                return $emptyStateLabel;
            }

            if ($component->hasOptionLabelFromRecordUsingCallback()) {
                return $component->getOptionLabelFromRecord($record);
            }

            return $record->getAttributeValue($component->getRelationshipTitleAttribute());
        });

        $this->getSelectedRecordUsing(static function (BelongsToParentSelect $component, $state) use ($modifyQueryUsing): ?Model {
            $relationship = Relation::noConstraints(fn () => $component->getRelationship());

            $relationshipQuery = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);

            $relationshipQuery->where($component->getQualifiedRelatedKeyNameForRelationship($relationship), $state);

            if ($modifyQueryUsing) {
                $relationshipQuery = $component->evaluate($modifyQueryUsing, [
                    'query' => $relationshipQuery,
                    'search' => null,
                ]) ?? $relationshipQuery;
            }

            return $relationshipQuery->first();
        });

        $this->getOptionLabelsUsing(static function (BelongsToParentSelect $component, array $values) use ($modifyQueryUsing): array {
            $relationship = Relation::noConstraints(fn () => $component->getRelationship());

            $relationshipQuery = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);

            $qualifiedRelatedKeyName = $component->getQualifiedRelatedKeyNameForRelationship($relationship);

            $relationshipQuery->whereIn($qualifiedRelatedKeyName, $values);

            if ($modifyQueryUsing) {
                $relationshipQuery = $component->evaluate($modifyQueryUsing, [
                    'query' => $relationshipQuery,
                    'search' => null,
                ]) ?? $relationshipQuery;
            }

            if ($component->hasOptionLabelFromRecordUsingCallback()) {
                return $relationshipQuery
                    ->get()
                    ->mapWithKeys(static fn (Model $record) => [
                        $record->{Str::afterLast($qualifiedRelatedKeyName, '.')} => $component->getOptionLabelFromRecord($record),
                    ])
                    ->toArray();
            }

            $relationshipTitleAttribute = $component->getRelationshipTitleAttribute();

            if (str_contains($relationshipTitleAttribute, '->')) {
                if (! str_contains($relationshipTitleAttribute, ' as ')) {
                    $relationshipTitleAttribute .= " as {$relationshipTitleAttribute}";
                }
            } else {
                $relationshipTitleAttribute = $relationshipQuery->qualifyColumn($relationshipTitleAttribute);
            }

            return $relationshipQuery
                ->pluck($relationshipTitleAttribute, $qualifiedRelatedKeyName)
                ->toArray();
        });

        $this->rule(
            static function (BelongsToParentSelect $component): Exists {
                $relationship = $component->getRelationship();

                return Rule::exists(
                    $relationship->getModel()::class,
                    $component->getQualifiedRelatedKeyNameForRelationship($relationship),
                );
            },
            static function (BelongsToParentSelect $component): bool {
                $relationship = $component->getRelationship();

                if (! (
                    $relationship instanceof BelongsTo ||
                    $relationship instanceof BelongsToThrough
                )) {
                    return false;
                }

                return ! $component->isMultiple();
            },
        );

        $this->saveRelationshipsUsing(static function (BelongsToParentSelect $component, Model $record, $state) use ($modifyQueryUsing) {
            $relationship = $component->getRelationship();

            if (
                ($relationship instanceof HasOneOrMany) ||
                ($relationship instanceof HasManyThrough) ||
                ($relationship instanceof BelongsToThrough)
            ) {
                return;
            }

            if (! $relationship instanceof BelongsToMany) {
                // If the model is new and the foreign key is already filled, we don't need to fill it again.
                // This could be a security issue if the foreign key was mutated in some way before it
                // was saved, and we don't want to overwrite that value.
                if (
                    $record->wasRecentlyCreated &&
                    filled($record->getAttributeValue($relationship->getForeignKeyName()))
                ) {
                    return;
                }

                $relationship->associate($state);
                $record->wasRecentlyCreated && $record->save();

                return;
            }

            if ($modifyQueryUsing) {
                $component->evaluate($modifyQueryUsing, [
                    'query' => $relationship->getQuery(),
                    'search' => null,
                ]);
            }

            /** @var Collection $relatedRecords */
            $relatedRecords = $relationship->getResults();

            $state = Arr::wrap($state ?? []);

            $recordsToDetach = array_diff(
                $relatedRecords
                    ->pluck($relationship->getRelatedKeyName())
                    ->map(static fn ($key): string => strval($key))
                    ->all(),
                $state,
            );

            if (count($recordsToDetach) > 0) {
                $relationship->detach($recordsToDetach);
            }

            $pivotData = $component->getPivotData();

            if ($pivotData === []) {
                $relationship->sync($state, detaching: false);

                return;
            }

            $relationship->syncWithPivotValues($state, $pivotData, detaching: false);
        });

        $this->createOptionUsing(static function (BelongsToParentSelect $component, array $data, Form $form) {
            $record = $component->getRelationship()->getRelated();
            $record->fill($data);
            $record->save();

            $form->model($record)->saveRelationships();

            return $record->getKey();
        });

        $this->fillEditOptionActionFormUsing(static function (BelongsToParentSelect $component): ?array {
            return $component->getSelectedRecord()?->attributesToArray();
        });

        $this->updateOptionUsing(static function (array $data, Form $form) {
            $form->getRecord()?->update($data);
        });

        $this->dehydrated(fn (BelongsToParentSelect $component): bool => ! $component->isMultiple());

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
