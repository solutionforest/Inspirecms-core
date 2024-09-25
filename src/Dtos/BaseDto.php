<?php

namespace SolutionForest\InspireCms\Dtos;

abstract class BaseDto
{
    public function __construct() {}

    /**
     * @return BaseDto
     */
    public static function fromArray(array $parameters)
    {
        $class = new \ReflectionClass(static::class);
        /**
         * @var BaseDto
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
