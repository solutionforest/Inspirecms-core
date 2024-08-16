<?php

namespace SolutionForest\InspireCms\DTOs;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class BaseDTO
{
    /**
     * @param Model $model
     */
    abstract public static function fromModel($model): static;
    abstract public function toArray(): array;

    public function __construct(array $parameters = [])
    {
        if (count($parameters) == 0) {
            return;
        }

        foreach ($parameters as $property => $value) {
            $this->{$property} = $value;
        }
    }

    public static function fromArray($data): static
    {
        return new static($data);
    }

    public function __get($name)
    {
        return $this->{$name} ?? null;
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;

        return $this;
    }
}