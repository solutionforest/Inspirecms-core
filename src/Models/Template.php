<?php

namespace SolutionForest\InspireCms\Models;

use SolutionForest\InspireCms\Events\Template\UpdateContent;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
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

    public function documentTypes()
    {
        return $this->morphedByMany(InspireCmsConfig::getDocumentTypeModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName());
    }

    public function contents()
    {
        return $this->morphedByMany(InspireCmsConfig::getContentModelClass(), 'templateable', InspireCmsConfig::getTemplateableTableName());
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

            $templateContent[$theme] = TemplateHelper::retrieveDefaultThemeContent();

            $this->content = $templateContent;

            event(new UpdateContent($this->withoutRelations(), $theme));
        }
    }

    /** {@inheritDoc} */
    public function getContent(?string $theme = null)
    {
        $theme ??= inspirecms_templates()->getCurrentTheme();

        // Create the template if it doesn't exist
        $this->initializeTemplate($theme);

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

    // region Dto
    public function toDto(...$args)
    {
        $theme = $args[0] ?? inspirecms_templates()->getCurrentTheme();

        return static::getDtoClass()::fromArray([
            'slug' => $this->slug,
            'theme' => $theme,
            'content' => $this->getContent($theme),
        ]);
    }

    public static function getDtoClass()
    {
        return \SolutionForest\InspireCms\Dtos\TemplateDto::class;
    }
    // endregion Dto

    public static function boot()
    {
        parent::boot();

        static::observe(TemplateObserver::class);
    }
}
