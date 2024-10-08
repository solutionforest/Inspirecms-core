<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModle of Model
 */
abstract class BaseModelDto extends BaseDto
{
    /**
     * @var ?TModle
     */
    protected $model;

    /**
     * @param TModle $model
     * @return self
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return TModle|null
     */
    public function getModel()
    {
        return $this->model;
    }
    
    /**
     * @return self
     */
    public static function fromArray(array $parameters)
    {
        return parent::fromArray($parameters);
    }

    /**
     * @param TModle $model
     * @return self
     */
    public static function fromModel($model)
    {
        return static::fromArray($model->attributesToArray())->setModel($model);
    }
}
