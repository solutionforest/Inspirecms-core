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
     * @var bool
     */
    public $isDefault;

    public static function fromArray(array $parameters)
    {
        if (isset($parameters['is_default'])) {
            $parameters['isDefault'] = (bool) $parameters['is_default'];
        } elseif (! isset($parameters['isDefault'])) {
            $parameters['isDefault'] = false;
        }

        return parent::fromArray($parameters);
    }

    /**
     * Get the label for the language.
     *
     * @param  string|null  $displayLocale  The locale to display the label in. If null, the default locale will be used.
     * @return string The label for the language.
     */
    public function getLabel($displayLocale = null)
    {
        if (is_null($this->code)) {
            return '';
        }

        $result = locale_get_display_name($this->code, $displayLocale);

        if ($result === false) {
            return $this->code;
        }

        return $result;
    }
}
