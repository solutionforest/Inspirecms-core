<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\Models\Field as BaseModel;
use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\Models\Contracts\Field as FieldContract;
use SolutionForest\InspireCms\Observers\FieldObserver;
use SolutionForest\InspireCms\Support\Helpers\RelationshipHelper;

class Field extends BaseModel implements FieldContract
{
    /** {@inheritDoc} */
    public function getFieldTypeConfigAttribute()
    {
        $fieldTypeConfig = FieldTypeHelper::getFieldTypeConfig($this->type);

        return $fieldTypeConfig?->getConfigNames() ?? [];
    }

    //region Dto
    public function toDto(...$args)
    {
        $dtoClass = static::getDtoClass();

        $dtoParameters['key'] = $this->name;
        $dtoParameters['group'] = $this->group_name ?? $this->group?->name;
        $dtoParameters['config'] = FilamentFieldGroup::getFieldTypeConfig($this->type, $this->config ?? []);

        return $dtoClass::fromArray($dtoParameters);
    }

    public static function getDtoClass()
    {
        return PropertyTypeDto::class;
    }
    //endregion Dto

    //region Scope(s)
    /** {@inheritDoc} */
    public function scopeByGroup($query, string $group)
    {
        return $query->whereHas('group', fn ($q) => $q->where('name', $group)->whereActive());
    }

    /** {@inheritDoc} */
    public function scopeWithGroupName($query)
    {
        $as = 'group';

        $column = 'name';

        static::joinGroupAs($query, $as);

        $query->addSelect("{$as}.{$column} as group_name");

        return $query;
    }

    protected static function joinGroupAs(&$query, $as, $joinType = 'leftJoin')
    {
        $relationName = 'group';

        return RelationshipHelper::joinRelationshipAs($query, $relationName, $as, $joinType);
    }
    //endregion Scope(s)

    public static function boot()
    {
        parent::boot();

        static::observe(FieldObserver::class);
    }
}
