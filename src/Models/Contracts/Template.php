<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;

/**
 * @template TDto of \SolutionForest\InspireCms\Dtos\TemplateDto
 *
 * @property string $slug
 * @property null | array<string,string> $content
 * @property ?\Carbon\CarbonInterface $created_at
 * @property ?\Carbon\CarbonInterface $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<Model & Templateable> $templateable
 * @property-read \Illuminate\Database\Eloquent\Collection<Model & DocumentType> $documentTypes
 * @property-read \Illuminate\Database\Eloquent\Collection<Model & Content> $content
 */
interface Template extends HasDtoModel
{
    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function templateable();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function documentTypes();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function content();

    /**
     * Initialize the template with the given theme.
     *
     * @param  string|null  $theme  The theme to initialize the template with. If null, a default theme will be used.
     * @return void
     */
    public function initializeTemplate();

    /**
     * Retrieve the content of the template.
     *
     * @param  string|null  $theme  The theme to use for retrieving the content. If null, the default theme will be used.
     * @return string The content of the template.
     */
    public function getContent(?string $theme = null);

    /**
     * Updates the content of the template.
     *
     * @param  string  $content  The new content to update the template with.
     * @param  string|null  $theme  Optional. The theme to apply to the content. Default is null.
     * @return void
     */
    public function updateContent($content, ?string $theme = null);

    /**
     * @return TDto The DTO representation of the model.
     */
    public function toDto(...$args);
}
