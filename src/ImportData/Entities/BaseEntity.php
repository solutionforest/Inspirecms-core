<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

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

    abstract public function getDataForModel(): array;


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
}
