<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

class TemplateDto extends BaseDto
{
    /**
     * @var string
     */
    public $slug;

    /**
     * @var string
     */
    public $theme;

    /**
     * @var string
     */
    public $content;
}
