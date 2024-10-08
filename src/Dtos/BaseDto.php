<?php

namespace SolutionForest\InspireCms\Dtos;

abstract class BaseDto
{
    public function __construct() {}

    /**
     * @return self
     */
    public static function fromArray(array $parameters)
    {
        $reflection = new \ReflectionClass(static::class);
        /**
         * @var BaseDto
         */
        $dto = $reflection->newInstanceWithoutConstructor();

        foreach ($parameters as $key => $value) {
            if (! $reflection->hasProperty($key)) {
                continue;
            }

            $property = $reflection->getProperty($key);
            if (! $property->isPublic()) {
                continue;
            }

            $dto->$key = $value;
        }

        return $dto;
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
