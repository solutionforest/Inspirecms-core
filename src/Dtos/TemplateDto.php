<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Support\Facades\Blade;
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

    public function render(array $data = []): string
    {
        return Blade::render($this->content, $data);
    }
}
