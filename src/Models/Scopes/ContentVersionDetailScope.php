<?php

namespace SolutionForest\InspireCms\Models\Scopes;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentVersionDetailScope implements Scope
{
    public function apply($builder, Model $model)
    {
        if ($model instanceof Content) {

            $query = $builder->getQuery();

            $foreignKey = $model->contentVersions()->getForeignKeyName();
            $related = $model->contentVersions()->getRelated();

            $recordCreationColumn = $related->getCreatedAtColumn();

            $t1TableName = '_cv_t1';
            $t1Q = DB::table($related->getTable(), $t1TableName)
                ->select('*')
                // sort by latest version
                ->orderByDesc($recordCreationColumn)
                ->orderBy($foreignKey);

            $t2TableName = '_cv_t2';
            $t2Q = DB::table($t1Q, $t2TableName)
                ->select($foreignKey) // for group by
                ->selectRaw(
                    $this->buildJsonGroupConcatExpression(
                        [
                            'id' => $related->getKeyName(),
                            'dt' => $recordCreationColumn,
                            'status' => 'publish_state',
                        ],
                        null,
                    ) . ' as __version_details'
                )
                ->selectRaw(
                    $this->buildJsonGroupConcatExpression(
                        'to_data',
                        $related->getKeyName(),
                    ) . ' as __version_data'
                )
                ->selectRaw("MAX($recordCreationColumn) as __latest_version_dt")
                ->selectRaw("MIN($recordCreationColumn) as __earliest_version_dt")
                ->groupBy($foreignKey);

            $joinTableName = '_cv';
            $query
                ->leftJoinSub($t2Q, $joinTableName, $model->getQualifiedKeyName(), '=', "$joinTableName.$foreignKey")
                ->addSelect([
                    $model->qualifyColumn('*'),
                    "{$joinTableName}.__latest_version_dt",
                    "{$joinTableName}.__earliest_version_dt",
                    "{$joinTableName}.__version_details",
                    "{$joinTableName}.__version_data",
                ]);

            $model->withCasts([
                '__version_details' => AsCollection::class,
                '__version_data' => AsCollection::class,
                '__latest_version_dt' => 'datetime',
                '__earliest_version_dt' => 'datetime',
            ]);
        }
    }

    private function buildJsonGroupConcatExpression(array | string $columns, ?string $jsonKeyColumn): string
    {
        $isJsonColumn = filled($jsonKeyColumn);

        // Check db driver
        $dbDriver = DB::connection()->getDriverName();

        if (is_string($columns)) {

            $gpConcatString = $columns;

        } else {

            $gpConcatStrings = collect($columns)
                ->map(function ($columnName, $jsonKey) use ($dbDriver) {

                    $columnTemplate = match ($dbDriver) {
                        // Haven't 'concat' function in sqlite
                        'sqlite' => "( '\"?\":\"' || ? || '\"' )",
                        default => "CONCAT('\"?\":\"', ?, '\"')",
                    };

                    return str($columnTemplate)
                        ->replaceArray('?', [$jsonKey, $columnName])
                        ->wrap('(', ')')
                        ->toString();
                })
                ->implode(match ($dbDriver) {
                    'sqlite' => " || ', ' || ",
                    default => ", ', ', ",
                });

            // Json format '{key1': 'value1', 'key2': 'value2'}'
            $gpConcatString = match ($dbDriver) {
                'sqlite' => "( '{' || $gpConcatStrings || '}' )",
                default => "CONCAT('{', $gpConcatStrings, '}')",
            };
        }

        if ($jsonKeyColumn) {

            $gpConcatString = str(
                match ($dbDriver) {
                    'sqlite' => "( '\"' || ? ||'\":' || ? )",
                    default => "CONCAT('\"', ?, '\":', ?, '')",
                }
            )
                ->replaceArray('?', [$jsonKeyColumn, $gpConcatString])
                ->toString();
        }

        // Not concat function
        if ($dbDriver === 'sqlite') {
            return $isJsonColumn
                ? "('{' || GROUP_CONCAT($gpConcatString) || '}')"
                : "('[' || GROUP_CONCAT($gpConcatString) || ']')";

        }

        return $isJsonColumn
            ? "CONCAT('{', GROUP_CONCAT($gpConcatString), '}')"
            : "CONCAT('[', GROUP_CONCAT($gpConcatString), ']')";
    }
}
