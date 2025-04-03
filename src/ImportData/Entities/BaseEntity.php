<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

/**
 * Class BaseEntity
 *
 * @template TEntity of BaseEntity
 *
 * @extends BaseDto<TEntity>
 */
abstract class BaseEntity extends BaseDto
{
    protected static array $rules = [];

    protected static array $propertiesOrder = [];

    protected static array $limitedProperties = [];

    abstract public function getDataForModel(): array;

    protected function initialize(): void
    {
        // Implement the initialization logic here
    }

    /** {@inheritDoc} */
    public static function fromArray(array $parameters)
    {
        // Preset default values for array properties
        $reflection = new \ReflectionClass(static::class);

        $propTypeMap = [];

        foreach ($reflection->getProperties() as $property) {

            if (! $property->isPublic()) {
                continue;
            }

            $propName = $property->getName();

            if (($propType = $property->getType())) {

                $propTypeName = $propType->getName();

                if ($propTypeName === 'array') {
                    $propTypeMap[$propName] = 'array';
                } elseif ($propTypeName === 'string') {
                    $propTypeMap[$propName] = 'string';
                } else {
                    $propTypeMap[$propName] = $propTypeName;
                }

                continue;
            }

            // Get type from PHPDoc @var annotation if reflection type is null

            // Try to get type from PHPDoc annotation
            $docComment = $property->getDocComment();

            if ($docComment && preg_match('/@var\s+([^\s]+)/', $docComment, $matches)) {
                $docType = $matches[1];

                // Use docType to determine if it's an array
                if (
                    // e.g. @var array
                    $docType === 'array' ||
                    // e.g. @var array<string>
                    strpos($docType, 'array<') === 0 ||
                    // e.g. @var string[]
                    strpos($docType, '[]') === strlen($docType) - 2
                ) {
                    $propTypeMap[$propName] = 'array';
                }
                // Use docType to determine if it's a string
                elseif ($docType === 'string') {
                    $propTypeMap[$propName] = 'string';
                }
            }
        }

        // Preset default values
        foreach ($propTypeMap as $name => $type) {
            switch ($type) {
                case 'array':
                    if (! isset($parameters[$name]) || ! is_array($parameters[$name])) {
                        $parameters[$name] = [];
                    }

                    break;

                case 'string':
                    // Is enum
                    if (isset($parameters[$name]) && $parameters[$name] instanceof \UnitEnum) {
                        $parameters[$name] = $parameters[$name]->value;
                    }
                    // Not string
                    elseif (isset($parameters[$name]) && ! is_string($parameters[$name])) {
                        $parameters[$name] = null;
                    }

                    break;

                default:
                    break;
            }
        }

        $result = parent::fromArray($parameters);

        // Initialize the entity
        $result->initialize();

        return $result;
    }

    /**
     * @param  Model  $record
     * @return TEntity
     */
    public static function fromRecord($record)
    {
        $data = $record->toArray();

        return static::fromArray(Arr::only($data, static::$limitedProperties));
    }

    /**
     * Validates the entity data.
     *
     * This method should be implemented to include the logic for validating
     * the data of the entity. It ensures that the entity's data meets the
     * required criteria before any further processing.
     *
     * @return void
     */
    public function validate()
    {
        if (empty($this->getValidationRules())) {
            return true;
        }

        $validator = validator($this->toArray(), $this->getValidationRules());

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return true;
    }

    protected function getValidationRules(): array
    {
        return static::$rules;
    }

    public function toExportArray(): array
    {
        return $this->toArray();
    }

    public function __toArray(): array
    {
        $order = static::$propertiesOrder;
        $limitFields = static::$limitedProperties;

        return collect(parent::__toArray())
            ->map(
                fn ($value) => (is_array($value) || $value instanceof Collection)
                    ? collect($value)->toArray()
                    : ($value instanceof BaseEntity ? $value->toArray() : $value)
            )
            // Limit the properties if the limit is defined
            ->when(
                ! empty($limitFields),
                fn (Collection $collection) => $collection
                    ->only($limitFields)
            )
            // Order the properties if the order is defined
            ->when(! empty($order), function (Collection $collection) use ($order) {
                $sorted = [];

                // First add items in the specified order
                foreach ($order as $key) {
                    if ($collection->has($key)) {
                        $sorted[$key] = $collection[$key];
                    }
                }

                // Then add any remaining items
                foreach ($collection as $key => $value) {
                    if (! array_key_exists($key, $sorted)) {
                        $sorted[$key] = $value;
                    }
                }

                return collect($sorted);
            })
            ->toArray();
    }
}
