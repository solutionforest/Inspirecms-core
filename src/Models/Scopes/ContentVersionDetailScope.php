<?php

namespace SolutionForest\InspireCms\Models\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentVersionDetailScope implements Scope
{
    public function apply($builder, Model $model)
    {
        if ($model instanceof Content) {

            $query = $builder->getQuery();

            $relatedFK = $model->contentVersions()->getForeignKeyName();
            $related = $model->contentVersions()->getRelated();
            $relatedPK = $related->getKeyName();

            $recordCreationColumn = $related->getCreatedAtColumn();

            $t1TableName = '_cv_t1';
            $t1Q = DB::table($related->getTable())
                ->orderByDesc($recordCreationColumn) // sort by created_at desc
                ->groupBy(
                    $relatedFK, // group by content_id
                    $recordCreationColumn, // include the ordered column in GROUP BY
                )
                ->select([
                    DB::raw("MAX($relatedPK) AS latest_version_id"),
                    $relatedFK,
                ]);

            $t2_1TableName = '_cv_t2_publish';
            $t2_2TableName = '_cv_t2_all';
            $t2_1Q = DB::table($related->getTable(), $t2_1TableName)
                ->joinSub(
                    $t1Q,
                    $t1TableName,
                    fn (JoinClause $join) => $join
                        ->on("$t1TableName.latest_version_id", '=', "$t2_1TableName.$relatedPK")
                )
                ->where("$t2_1TableName.publish_state", 'publish')
                ->select([
                    "$t2_1TableName.$relatedFK",
                    "$t2_1TableName.publish_state",
                    "$t2_1TableName.$relatedPK",
                    "$t2_1TableName.$recordCreationColumn",
                    "$t2_1TableName.to_data AS data",
                ]);
            $t2_2Q = DB::table($related->getTable(), $t2_2TableName)
                ->joinSub(
                    $t1Q,
                    $t1TableName,
                    fn (JoinClause $join) => $join
                        ->on("$t1TableName.latest_version_id", '=', "$t2_2TableName.$relatedPK")
                )
                ->whereNot("$t2_2TableName.publish_state", 'publish')
                ->select([
                    "$t2_2TableName.$relatedFK",
                    "$t2_2TableName.publish_state",
                    "$t2_2TableName.$relatedPK",
                    "$t2_2TableName.$recordCreationColumn",
                    "$t2_2TableName.to_data AS data",
                ]);

            $query
                ->leftJoinSub(
                    $t2_1Q,
                    $t2_1TableName,
                    fn (JoinClause $join) => $join
                        ->on($model->getQualifiedKeyName(), '=', "{$t2_1TableName}.{$relatedFK}")
                )
                ->leftJoinSub(
                    $t2_2Q,
                    $t2_2TableName,
                    fn (JoinClause $join) => $join
                        ->on($model->getQualifiedKeyName(), '=', "{$t2_2TableName}.{$relatedFK}")
                )
                ->addSelect($model->qualifyColumn('*'))
                ->addSelect([
                    DB::raw("{$t2_1TableName}.{$relatedPK} AS __latest_version_publish_id"),
                    DB::raw("{$t2_1TableName}.{$recordCreationColumn} AS __latest_version_publish_dt"),
                    DB::raw("{$t2_1TableName}.data AS __latest_version_publish_data"),
                ])
                ->addSelect([
                    DB::raw("{$t2_2TableName}.{$relatedPK} AS __latest_version_id"),
                    DB::raw("{$t2_2TableName}.{$recordCreationColumn} AS __latest_version_dt"),
                    DB::raw("{$t2_2TableName}.data AS __latest_version_data"),
                ]);

            $model->withCasts([
                '__latest_version_publish_dt' => 'datetime',
                '__latest_version_dt' => 'datetime',

                '__latest_version_publish_data' => 'json',
                '__latest_version_data' => 'json',
            ]);
        }
    }
}
