<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class BaseDto
{
    /**
     * @param  Model  $model
     * @return BaseDto<TModel>
     */
    abstract public static function fromModel($model);

    public function __construct() {}

    /**
     * @return BaseDto<TModel>
     */
    public static function fromArray(array $parameters)
    {
        $class = new \ReflectionClass(static::class);
        /**
         * @var BaseDto<TModel>
         */
        $dto = $class->newInstanceWithoutConstructor();

        foreach ($parameters as $key => $value) {
            $dto->$key = $value;
        }

        return $dto;
    }

    public function __get($name): mixed
    {
        return $this->{$name} ?? null;
    }

    public function __set($name, $value): void
    {
        $this->{$name} = $value;
    }

    public function __toArray(): array
    {
        return get_object_vars($this);
    }

    public function toArray(): array
    {
        return (array) $this;
    }
}
