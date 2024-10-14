<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Events\CreateTemplate;
use SolutionForest\InspireCms\Models\Contracts\Template as TemplateContract;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class Template extends BaseModel implements TemplateContract
{
    protected $guarded = ['id'];

    public function templateable(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getTemplateableModelClass(), 'template_id');
    }

    public function documentTypes(): MorphToMany
    {
        return $this->morphedByMany(InspireCmsConfig::getDocumentTypeModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName());
    }

    public function content(): MorphToMany
    {
        return $this->morphedByMany(InspireCmsConfig::getContentModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName());
    }

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

    public function getViewFullName(): string
    {
        return str($this->getTemplateDirectory())
            ->rtrim('/')
            ->finish('/')
            ->finish($this->slug)
            ->after(resource_path('views'))
            ->ltrim('/')
            ->replace('/', '.')
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

            $model->path = $model->performTemplatePath();

        });
    }

    protected function performTemplatePath(): string
    {
        return str($this->slug)
            ->trim()
            ->snake()
            ->replace(['-', ' '], '-')
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
