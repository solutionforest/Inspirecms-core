<?php

namespace SolutionForest\InspireCms\Dtos\Assets;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

class FileDto extends BaseDto
{
    /**
     * @var ?string
     */
    public $disk;
    
    /**
     * @var string
     */
    public $path;
    
    /**
     * @var ?string
     */
    public $directory;

    public function getFullPath()
    {
        return $this->directory ? $this->directory . '/' . $this->path : $this->path;
    }
}
