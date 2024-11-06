<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use SolutionForest\FilamentFieldGroup\Models\Contracts\Field as BaseContract;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;

interface Field extends BaseContract, HasDtoModel
{
    /**
     * Get the state path with group.
     *
     * This method should return the state path including the group information as a string.
     *
     * @return string The state path with group.
     */
    public function getStatePathWithGroup(): string;

    /**
     * Get the configuration for the field type.
     *
     * @return array The configuration settings for the field type.
     */
    public function getFieldTypeConfigAttribute();
}
