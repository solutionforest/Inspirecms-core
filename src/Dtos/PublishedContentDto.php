<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

class PublishedContentDto extends BaseDto
{
    /**
     * @var ContentDto
     */
    public $content;

    /**
     * @var TemplateDto
     */
    public $template;

    /**
     * @var string
     */
    public $locale;

    public $parameters = [];
}
