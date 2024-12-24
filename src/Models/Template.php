<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template as TemplateContract;
use SolutionForest\InspireCms\Observers\TemplateObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class Template extends BaseModel implements TemplateContract
{
    protected $guarded = ['id'];

    protected $casts = [
        'content' => 'array',
    ];

    public function templateable()
    {
        return $this->hasMany(InspireCmsConfig::getTemplateableModelClass(), 'template_id');
    }

    /** {@inheritDoc} */
    public function initializeTemplate(?string $theme = null)
    {
        $templateContent = $this->content ?? [];

        if (! is_array($templateContent)) {
            $templateContent = [];
        }

        $theme ??= inspirecms_templates()->getCurrentTheme();

        if (empty($templateContent) || ! isset($templateContent[$theme])) {

            $templateContent[$theme] = inspirecms_templates()->retrieveDefaultContent();

            $this->content = $templateContent;

            event(new \SolutionForest\InspireCms\Events\Template\UpdateContent($this->withoutRelations(), $theme));
        }
    }

    /** {@inheritDoc} */
    public function getContent(?string $theme = null)
    {
        $theme ??= inspirecms_templates()->getCurrentTheme();

        return data_get($this->content ?? [], $theme) ?? '';
    }

    /** {@inheritDoc} */
    public function updateContent($content, ?string $theme = null)
    {
        $templateContent = $this->content ?? [];
        if (! is_array($templateContent)) {
            $templateContent = [];
        }

        $templateContent[$theme ?? inspirecms_templates()->getCurrentTheme()] = $content;

        $this->content = $templateContent;

        $this->save();
    }

    public static function boot()
    {
        parent::boot();

        static::observe(TemplateObserver::class);
    }
}
