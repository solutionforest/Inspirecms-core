<?php

namespace SolutionForest\InspireCms\Helpers;

use Filament\Support\Services\RelationshipJoiner;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use function Filament\Support\generate_search_column_expression;
use function Filament\Support\generate_search_term_expression;

class SearchHelper
{
    /**
     * Get the options for attaching a relationship.
     *
     * @param  BelongsToMany  $relationship  The relationship to get attachment options for.
     * @param  string  $inverseRelationshipName  The name of the inverse relationship.
     * @param  int  $optionsLimit  The limit on the number of options to retrieve.
     * @param  callable(Model)  $getRecordTitleUsing  A callback function to get the record title.
     * @param  string|null  $search  An optional search term to filter the options.
     * @param  array  $searchColumns  An array of columns to search within.
     * @param  bool  $isForcedCaseInsensitive  Whether to force case-insensitive search.
     * @param  array  $excepts  The records to exclude from the options.
     * @return array The options for attaching the relationship.
     */
    public static function getAttachOptions($relationship, $inverseRelationshipName, int $optionsLimit, $getRecordTitleUsing, ?string $search = null, $searchColumns = [], $isForcedCaseInsensitive = false, $excepts = []): array
    {
        $relationshipQuery = static::initializeSearchQuery($relationship, $optionsLimit, $search, $searchColumns, $isForcedCaseInsensitive);

        $relationCountHash = $relationship->getRelationCountHash(incrementJoinCount: false);

        $relationshipQuery
            ->whereDoesntHave(
                $inverseRelationshipName,
                fn (Builder $query): Builder => $query->where(
                    // https://github.com/filamentphp/filament/issues/8067
                    $relationship->getParent()->getTable() === $relationship->getRelated()->getTable() ?
                        "{$relationCountHash}.{$relationship->getParent()->getKeyName()}" :
                        $relationship->getParent()->getQualifiedKeyName(),
                    $relationship->getParent()->getKey(),
                ),
            );

        if ($excepts) {
            $relationshipQuery->whereNotIn($relationship->getRelated()->getQualifiedKeyName(), $excepts);
        }

        $relatedKeyName = $relationship->getRelatedKeyName();

        return $relationshipQuery
            ->get()
            ->mapWithKeys(fn (Model $record): array => [$record->{$relatedKeyName} => $getRecordTitleUsing($record)])
            ->all();
    }

    /**
     * Get the options for attaching a relationship. (Skip the inverse relationship check)
     *
     * @param  BelongsToMany  $relationship  The relationship to get attachment options for.
     * @param  int  $optionsLimit  The limit on the number of options to retrieve.
     * @param  callable(Model)  $getRecordTitleUsing  A callback function to get the record title.
     * @param  string|null  $search  An optional search term to filter the options.
     * @param  array  $searchColumns  An array of columns to search within.
     * @param  bool  $isForcedCaseInsensitive  Whether to force case-insensitive search.
     * @param  array  $excepts  The records to exclude from the options.
     * @return array The options for attaching the relationship.
     */
    public static function getAttachOptionsIgnoringInverse($relationship, int $optionsLimit, $getRecordTitleUsing, ?string $search = null, $searchColumns = [], $isForcedCaseInsensitive = false, $excepts = []): array
    {
        $relationshipQuery = static::initializeSearchQuery($relationship, $optionsLimit, $search, $searchColumns, $isForcedCaseInsensitive);

        if ($excepts) {
            $relationshipQuery->whereNotIn($relationship->getRelated()->getQualifiedKeyName(), $excepts);
        }

        $relatedKeyName = $relationship->getRelatedKeyName();

        return $relationshipQuery
            ->get()
            ->mapWithKeys(fn (Model $record): array => [$record->{$relatedKeyName} => $getRecordTitleUsing($record)])
            ->all();
    }

    /**
     * @param  BelongsToMany  $relationship
     * @param  int  $optionsLimit
     * @param  string|null  $search
     * @param  array  $searchColumns
     * @param  bool  $isForcedCaseInsensitive
     * @return Builder
     */
    private static function initializeSearchQuery($relationship, $optionsLimit, $search, $searchColumns, $isForcedCaseInsensitive)
    {
        $relationshipQuery = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);

        if (! isset($relationshipQuery->getQuery()->limit)) {
            $relationshipQuery->limit($optionsLimit);
        }

        return static::filterBySearch($relationshipQuery, $search, $searchColumns, $isForcedCaseInsensitive);
    }

    /**
     * @param  Builder  $query
     * @param  string|null  $search  An optional search term to filter the options.
     * @param  array  $searchColumns  An array of columns to search within.
     * @param  bool  $isForcedCaseInsensitive  Whether to force case-insensitive search.
     * @return Builder
     */
    public static function filterBySearch($query, $search, $searchColumns, $isForcedCaseInsensitive)
    {
        if (filled($search) && $searchColumns) {
            /** @var Connection $databaseConnection */
            $databaseConnection = $query->getConnection();

            $search = generate_search_term_expression($search, $isForcedCaseInsensitive, $databaseConnection);

            $isFirst = true;

            $query->where(function (Builder $query) use ($databaseConnection, $isFirst, $isForcedCaseInsensitive, $searchColumns, $search): Builder {
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

        return $query;
    }

    /**
     * @param  Builder  $query
     * @param  string|null  $search  An optional search term to filter the options.
     * @param  array  $searchColumns  An array of columns to search within.
     * @param  bool  $isForcedCaseInsensitive  Whether to force case-insensitive search.
     * @param bool $isFirst
     * 
     * @return Builder
     */
    public static function globalSearchWithRelation($query, $search, $searchAttributes, $isForcedCaseInsensitive, bool &$isFirst)
    {
        /** @var Connection $databaseConnection */
        $databaseConnection = $query->getConnection();

        foreach ($searchAttributes as $searchAttribute) {

            $whereClause = $isFirst ? 'where' : 'orWhere';

            $isRelationColumn = str($searchAttribute)->contains('.') // Check if the search attribute is a relation column
                && str($searchAttribute)->afterLast('.') != 'id'; // Check if the search attribute is not an id column

            $query->when(
                $isRelationColumn,
                function (Builder $query) use ($databaseConnection, $isForcedCaseInsensitive, $searchAttribute, $search, $whereClause): Builder {
                    return $query->{"{$whereClause}Relation"}(
                        (string) str($searchAttribute)->beforeLast('.'),
                        generate_search_column_expression((string) str($searchAttribute)->afterLast('.'), $isForcedCaseInsensitive, $databaseConnection),
                        'like',
                        "%{$search}%",
                    );
                },
                fn (Builder $query) => $query->{$whereClause}(
                    generate_search_column_expression($searchAttribute, $isForcedCaseInsensitive, $databaseConnection),
                    'like',
                    "%{$search}%",
                ),
            );

            $isFirst = false;
        }

        return $query;
    }
}
