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
     * @return static
     */
    abstract public static function fromModel($model): static;

    public static function fromArray(array $parameters): static
    {
        $class = new \ReflectionClass(static::class);
        $object = $class->newInstanceWithoutConstructor();

        foreach ($parameters as $key => $value) {
            $object->$key = $value;
        }

        return $object;
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
