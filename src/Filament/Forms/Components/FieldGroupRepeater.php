<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Exception;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Services\RelationshipJoiner;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

use function Filament\Support\generate_search_column_expression;
use function Filament\Support\generate_search_term_expression;

class FieldGroupRepeater extends Repeater
{
    protected ?string $fieldGroupRelationName = null;

    protected ?string $fieldGroupRecordTitleAttribute = null;

    protected ?string $fieldGroupRecordOrderAttribute = null;

    protected ?Closure $itemStateFromAttachFieldGroupUsing = null;

    protected ?Closure $modifyRecordSelectOptionQueryUsing = null;

    protected ?Closure $modifyRecordSelectUsing = null;

    protected ?array $recordSelectSearchColumns = null;

    protected bool | Closure | null $isSearchForcedCaseInsensitive = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultItems(0);

        $this->relationship('morphFieldGroups');
        $this->fieldGroupRelationName('fieldGroups', 'title', 'sort');
        $this->live();
        $this->addable(true);

        // Avoid duplicate fieldGroup
        $this->cloneable(false);
    }

    public function fieldGroupRelationName(string $relationName, ?string $titleAttribute = null, ?string $orderAttribute = null): static
    {
        $this->fieldGroupRelationName = $relationName;
        $this->fieldGroupRecordTitleAttribute($titleAttribute);
        $this->fieldGroupRecordOrderAttribute($orderAttribute);

        return $this;
    }

    public function fieldGroupRecordTitleAttribute(string $titleAttribute): static
    {
        $this->fieldGroupRecordTitleAttribute = $titleAttribute;

        return $this;
    }

    public function fieldGroupRecordOrderAttribute(string $orderAttribute): static
    {
        $this->fieldGroupRecordOrderAttribute = $orderAttribute;

        return $this;
    }

    public function itemStateFromAttachFieldGroupUsing(?Closure $callback): static
    {
        $this->itemStateFromAttachFieldGroupUsing = $callback;

        return $this;
    }

    public function modifyRecordSelectOptionQueryUsing(?Closure $callback): static
    {
        $this->modifyRecordSelectOptionQueryUsing = $callback;

        return $this;
    }

    public function modifyRecordSelectUsing(?Closure $callback): static
    {
        $this->modifyRecordSelectUsing = $callback;

        return $this;
    }

    public function recordSelectSearchColumns(array $columns): static
    {
        $this->recordSelectSearchColumns = $columns;

        return $this;
    }

    public function forceSearchCaseInsensitive(bool | Closure | null $condition = true): static
    {
        $this->isSearchForcedCaseInsensitive = $condition;

        return $this;
    }

    public function getFieldGroupRelationName(): string
    {
        return $this->fieldGroupRelationName ?? 'fieldGroups';
    }

    public function getFieldGroupTitleAttribute(): string
    {
        return $this->fieldGroupRecordTitleAttribute ?? 'title';
    }

    public function getItemStateFromAttachFieldGroupUsing(): ?Closure
    {
        return $this->itemStateFromAttachFieldGroupUsing;
    }

    public function getFieldGroupRelationship()
    {
        return Relation::noConstraints(
            fn () => $this->getModelInstance()->{$this->getFieldGroupRelationName()}()
        );
    }

    public function getFieldGroupRelationshipQuery()
    {
        $relationship = $this->getFieldGroupRelationship();

        return app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);
    }

    public function getFieldGroupRecordTitleAttribute(): string
    {
        return $this->evaluate($this->fieldGroupRecordTitleAttribute) ?? 'title';
    }

    public function getFieldGroupRecordOrderAttribute(): string
    {
        return $this->evaluate($this->fieldGroupRecordOrderAttribute) ?? 'sort';
    }

    public function getRecordSelect(): Select
    {
        $relationship = $this->getFieldGroupRelationship();

        $relationshipQuery = $this->getFieldGroupRelationshipQuery();

        $getOptions = function (int $optionsLimit, ?string $search = null, ?array $searchColumns = []) use ($relationship, $relationshipQuery): array {

            $qualifiedRelatedKeyName = $relationship->getQualifiedRelatedKeyName();
            $keyColumn = Str::afterLast($qualifiedRelatedKeyName, '.');

            if ($this->modifyRecordSelectOptionQueryUsing) {
                $relationshipQuery->where(function (Builder $query) {
                    return $this->evaluate($this->modifyRecordSelectOptionQueryUsing, [
                        'query' => $query,
                    ]);
                });
            }

            if (! isset($relationshipQuery->getQuery()->limit)) {
                $relationshipQuery->limit($optionsLimit);
            }

            $titleAttribute = $this->getFieldGroupRecordTitleAttribute();
            $titleAttribute = filled($titleAttribute) ? $relationshipQuery->qualifyColumn($titleAttribute) : null;

            if (filled($search) && ($searchColumns || filled($titleAttribute))) {
                /** @var Connection $databaseConnection */
                $databaseConnection = $relationshipQuery->getConnection();

                $isForcedCaseInsensitive = $this->isSearchForcedCaseInsensitive();

                $search = generate_search_term_expression($search, $isForcedCaseInsensitive, $databaseConnection);
                $searchColumns ??= [$titleAttribute];

                $isFirst = true;

                $relationshipQuery->where(function (Builder $query) use ($databaseConnection, $isFirst, $isForcedCaseInsensitive, $searchColumns, $search): Builder {
                    foreach ($searchColumns as $searchColumn) {
                        $whereClause = $isFirst ? 'where' : 'orWhere';

                        $query->{$whereClause}(
                            generate_search_column_expression($query->qualifyColumn($searchColumn), $isForcedCaseInsensitive, $databaseConnection),
                            'like',
                            "%{$search}%",
                        );

                        $isFirst = false;
                    }

                    return $query;
                });
            }

            $orderAttribute = $this->getFieldGroupRecordOrderAttribute();
            $orderAttribute = filled($orderAttribute) ? $relationshipQuery->qualifyColumn($orderAttribute) : null;
            if (empty($relationshipQuery->getQuery()->orders)) {
                $relationshipQuery->orderBy($orderAttribute);
            }

            return $relationshipQuery
                ->pluck($titleAttribute, $keyColumn)
                ->all();
        };

        $select = Select::make('recordId')
            ->hiddenLabel()
            ->options(fn () => $getOptions(50))
            ->required()->searchable($this->getRecordSelectSearchColumns() ?? true)
            ->getSearchResultsUsing(static fn (Select $component, string $search): array => $getOptions(
                optionsLimit: $component->getOptionsLimit(),
                search: $search,
                searchColumns: $component->getSearchColumns()
            ))
            ->getOptionLabelUsing(function ($value) use ($relationshipQuery): string {
                return $this->getRecordTitle($relationshipQuery->find($value));
            })
            ->getOptionLabelsUsing(function (array $values) use ($relationshipQuery): array {
                return $relationshipQuery->find($values)
                    ->mapWithKeys(fn (Model $record): array => [$record->getKey() => $this->getRecordTitle($record)])
                    ->all();
            })
            ->options(fn (Select $component): array => $getOptions(optionsLimit: $component->getOptionsLimit()));

        if ($this->modifyRecordSelectUsing) {
            $select = $this->evaluate($this->modifyRecordSelectUsing, [
                'select' => $select,
            ]);
        }

        return $select;
    }

    public function getRecordSelectSearchColumns(): array
    {
        return $this->recordSelectSearchColumns ?? [$this->getFieldGroupTitleAttribute()];
    }

    public function isSearchForcedCaseInsensitive(): ?bool
    {
        return $this->evaluate($this->isSearchForcedCaseInsensitive);
    }

    public function getAddAction(): Action
    {
        $action = Action::make($this->getAddActionName())
            ->label(fn (Repeater $component) => $component->getAddActionLabel())
            ->color('gray')
            ->form(function (FieldGroupRepeater $component, Form $form): array | Form | null {
                return $form
                    ->model($component->getFieldGroupRelationship()->getModel()::class)
                    ->schema(fn () => [
                        $this->getRecordSelect(),
                    ]);
            })
            ->action(function (FieldGroupRepeater $component, array $data, Form $form): void {

                if (! $component->getItemStateFromAttachFieldGroupUsing()) {
                    throw new Exception("FieldGroupRepeater field [{$component->getStatePath()}] must have a [itemStateFromAttachFieldGroupUsing()] closure set.");
                }

                $itemState = $component->evaluate($component->getItemStateFromAttachFieldGroupUsing(), [
                    'data' => $data,
                    'form' => $form,
                ]) ?? [];

                $newUuid = $component->generateUuid();

                $items = $component->getState();

                if ($newUuid) {
                    $items[$newUuid] = $itemState;
                } else {
                    $items[] = $itemState;
                }

                $component->state($items);

                $component->getChildComponentContainer($newUuid ?? array_key_last($items))->fill($itemState);

                $component->collapsed(false, shouldMakeComponentCollapsible: false);

                $component->callAfterStateUpdated();
            })
            ->button()
            ->size(ActionSize::Medium)
            ->visible(fn (Repeater $component): bool => $component->isAddable());

        if ($this->modifyAddActionUsing) {
            $action = $this->evaluate($this->modifyAddActionUsing, [
                'action' => $action,
            ]) ?? $action;
        }

        return $action;
    }

    public function getAddActionName(): string
    {
        return 'attach';
    }
}
