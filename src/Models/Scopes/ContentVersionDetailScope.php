<?php

namespace SolutionForest\InspireCms\Models\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentVersionDetailScope implements Scope
{
    public function apply($builder, Model $model)
    {
        if ($model instanceof Content) {

            $query = $builder->getQuery();

            $cvModel = $model->contentVersions()->getRelated();
            /**
             * @var string cms_content_verions's **content_id**
             */
            $cvFK = $model->contentVersions()->getForeignKeyName();
            /**
             * @var string cms_content_verions's **id**
             */
            $cvPK = $cvModel->getKeyName();
            $cvCreationColumn = $cvModel->getCreatedAtColumn();

            /**
             * @var Model
             */
            $cvPublishedModel = $cvModel->publishLog()->getRelated();
            /**
             * @var string cms_content_verions's publish_log's **version_id**
             */
            $cvPublishedFK = $cvModel->publishLog()->getForeignKeyName();

            /**
             * @var Builder Content Version Query group by content_id (fetch latest version id `joined_version_id`)
             */
            $baseQ = DB::table($cvModel->getTable())
                ->groupBy($cvFK)
                ->select([
                    DB::raw("MAX($cvPK) AS joined_version_id"),
                    $cvFK,
                ]);

            $cvAllQ = DB::table($cvModel->getTable(), '_cv_t2_all')
                ->joinSub(
                    $baseQ,
                    '_cv_t1_base',
                    fn (JoinClause $join) => $join
                        ->on('_cv_t1_base.joined_version_id', '=', "_cv_t2_all.$cvPK")
                )
                ->select('_cv_t2_all.*');

            $cvPublishedQ = DB::table($cvModel->getTable(), '_cv_t2_p')
                ->joinSub(
                    $baseQ,
                    '_cv_t1_base',
                    fn (JoinClause $join) => $join
                        ->on('_cv_t1_base.joined_version_id', '=', "_cv_t2_p.$cvPK")
                )
                // Where exist publish log
                ->whereExists(
                    fn (Builder | \Illuminate\Database\Eloquent\Builder $query) => $query
                        ->select()
                        ->from($cvPublishedModel->getTable(), '_cv_base_p')
                        // content version id = publish log version id
                        ->whereRaw("_cv_t2_p.{$cvPK} = _cv_base_p.$cvPublishedFK")
                        ->whereRaw('_cv_base_p.published_at <= ?', [now()])
                )
                ->select('_cv_t2_p.*');

            $cvAllTableName = '_cv_all';
            $cvPublishedTableName = '_cv_published';
            $query
                ->leftJoinSub(
                    $cvAllQ,
                    $cvAllTableName,
                    fn (JoinClause $join) => $join
                        ->on($model->getQualifiedKeyName(), '=', "$cvAllTableName.$cvFK")
                )
                ->addSelect([
                    DB::raw("{$cvAllTableName}.{$cvPK} AS __latest_version_id"),
                    DB::raw("{$cvAllTableName}.{$cvCreationColumn} AS __latest_version_dt"),
                    DB::raw("{$cvAllTableName}.to_data AS __latest_version_data"),
                ])
                ->leftJoinSub(
                    $cvPublishedQ,
                    $cvPublishedTableName,
                    fn (JoinClause $join) => $join
                        ->on($model->getQualifiedKeyName(), '=', "$cvPublishedTableName.$cvFK")
                )
                ->addSelect([
                    DB::raw("{$cvPublishedTableName}.{$cvPK} AS __latest_version_publish_id"),
                    DB::raw("{$cvPublishedTableName}.{$cvCreationColumn} AS __latest_version_publish_dt"),
                    DB::raw("{$cvPublishedTableName}.to_data AS __latest_version_publish_data"),
                ])
                ->addSelect($model->qualifyColumn('*'));

            $model->withCasts([
                '__latest_version_publish_dt' => 'datetime',
                '__latest_version_dt' => 'datetime',

                '__latest_version_publish_data' => 'json',
                '__latest_version_data' => 'json',
            ]);
        }
    }
}
