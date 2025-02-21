<?php

namespace SolutionForest\InspireCms\Fields\Dtos;

use Illuminate\Support\Facades\Storage;
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

    public function getUrl(): ?string
    {
        $fs = filled($this->disk) ? Storage::disk($this->disk) : Storage::getDriver();

        return $fs->url($this->getFullPath());
    }

    public function __toString()
    {
        return $this->getUrl();
    }
}
