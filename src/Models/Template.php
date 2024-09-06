<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Events\CreateTemplate;
use SolutionForest\InspireCms\Models\Contracts\Template as TemplateContract;

class Template extends BaseModel implements TemplateContract
{
    protected $guarded = ['id'];

    public function isFileCreated(): bool
    {
        $fullpath = $this->getFileFullPath();

        return file_exists($fullpath);
    }

    public function createTemplateFile(): void
    {
        $fullpath = $this->getFileFullPath();

        // Create file if not exists
        if (! file_exists($fullpath)) {
            file_put_contents($fullpath, '');
        }
    }

    public function getFileFullPath(): string
    {
        return str($this->ensureDirectoryExists($this->getTemplateDirectory()))
            ->rtrim('/')
            ->finish('/')
            ->finish($this->path)
            ->toString();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {

            $model->path = $model->performTemplatePath();

            $model->createTemplateFile();

            event(new CreateTemplate($model));

        });
        static::saving(function (self $model) {
            ray($model);

            $model->path = $model->performTemplatePath();

        });
    }

    protected function performTemplatePath(): string
    {
        return str($this->name)
            ->trim()
            ->snake()
            ->replace(['-', ' '], '_')
            ->trim('.')
            ->finish('.blade.php')
            ->toString();
    }

    protected function ensureDirectoryExists(string $dir): string
    {
        // Create dir if not exists
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    protected function getTemplateDirectory(): string
    {
        return config('inspirecms.template.path');
    }
}
