<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template as TemplateContract;
use SolutionForest\InspireCms\Observers\TemplateObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

/**
 * @implements TemplateContract<Template>
 */
class Template extends BaseModel implements TemplateContract
{
    protected $guarded = ['id'];

    protected ?string $preloadTemplateContent = null;

    public function templateable()
    {
        return $this->hasMany(InspireCmsConfig::getTemplateableModelClass(), 'template_id');
    }

    public function documentTypes()
    {
        return $this->morphedByMany(InspireCmsConfig::getDocumentTypeModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName());
    }

    public function content()
    {
        return $this->morphedByMany(InspireCmsConfig::getContentModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName());
    }

    public function isFileCreated()
    {
        $fullpath = $this->getFileFullPath();

        return file_exists($fullpath);
    }

    public function createTemplateFile()
    {
        $fullpath = $this->getFileFullPath();

        // Create file if not exists
        if (! file_exists($fullpath)) {

            $content = $this->preloadTemplateContent;
            if (blank($content)) {
                $content = '<div>Template content</div>';
            }
            file_put_contents($fullpath, $content);
        }
    }

    public function getFileFullPath()
    {
        return str($this->ensureDirectoryExists($this->getTemplateDirectory()))
            ->rtrim('/')
            ->finish('/')
            ->finish($this->path)
            ->toString();
    }

    public function getViewFullName()
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

    public function retrieveTemplatePath()
    {
        return str($this->slug)
            ->trim()
            ->snake()
            ->replace(['-', ' '], '-')
            ->trim('.')
            ->finish('.blade.php')
            ->toString();
    }

    public function preloadTemplateContentBeforeCreate($content)
    {
        $this->preloadTemplateContent = $content;

        return $this;
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

    public static function boot()
    {
        parent::boot();

        static::observe(TemplateObserver::class);
    }
}
