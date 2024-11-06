<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\Models\Field as BaseModel;
use SolutionForest\InspireCms\Dtos\FieldDto;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\Models\Contracts\Field as FieldContract;
use SolutionForest\InspireCms\Observers\FieldObserver;

#[ObservedBy(FieldObserver::class)]
class Field extends BaseModel implements FieldContract
{
    public function getStatePathWithGroup(): string
    {
        return implode('.', [$this->group?->name, $this->name]);
    }

    public function getFieldTypeConfigAttribute()
    {
        $fieldTypeConfig = FieldTypeHelper::getFieldTypeConfig($this->type);

        return $fieldTypeConfig?->getConfigNames() ?? [];
    }

    //region Dto
    public function toDto(...$args)
    {
        $dtoClass = static::getDtoClass();
        $dtoParameters = $this->toArray();
        $dtoParameters['config'] = FilamentFieldGroup::getFieldTypeConfig($this->type, $this->config ?? []);

        return $dtoClass::fromArray($dtoParameters);
    }

    public static function getDtoClass(): string
    {
        return FieldDto::class;
    }
    //endregion Dto
}
