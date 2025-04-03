<?php

namespace SolutionForest\InspireCms\Models\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Models\Contracts\Content;

class DocumentTypeScope implements Scope
{
    public function apply($builder, Model $model)
    {
        if ($model instanceof Content) {
            
            $query = $builder->getQuery();

            $related = $model->documentType()->getRelated();
            $foreignKey = $model->documentType()->getForeignKeyName();

            $query
                ->leftJoin(
                    $related->getTable() . ' as _dt', 
                    $model->qualifyColumn($foreignKey),
                    '=',
                    '_dt.' . $related->getKeyName()
                )
                ->addSelect([
                    $model->qualifyColumn('*'),
                    DB::raw('_dt.category as __document_type_cat'),
                    DB::raw('_dt.slug as __document_type_slug'),
                ]);
        }
    }
}
