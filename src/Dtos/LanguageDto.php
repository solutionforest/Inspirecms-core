<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

/**
 * @extends BaseDto<LanguageDto>
 */
class LanguageDto extends BaseDto
{
    /**
     * @var string|int
     */
    public $id;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $isDefault;

    public static function fromArray(array $parameters)
    {
        if (! isset($parameters['label'])) {
            $parameters['label'] = $parameters['code'];
        }

        if (isset($parameters['is_default'])) {
            $parameters['isDefault'] = (bool) $parameters['is_default'];
        } elseif (! isset($parameters['isDefault'])) {
            $parameters['isDefault'] = false;
        }

        return parent::fromArray($parameters);
    }
}
