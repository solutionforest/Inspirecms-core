<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;

/**
 * @template TDto of \SolutionForest\InspireCms\Dtos\TemplateDto
 *
 * @property string $slug
 * @property null | array<string,string> $content
 * @property ?CarbonInterface $created_at
 * @property ?CarbonInterface $updated_at
 * @property-read Collection<Model & Templateable> $templateable
 * @property-read Collection<Model & DocumentType> $documentTypes
 * @property-read Collection<Model & Content> $contents
 */
interface Template extends HasDtoModel
{
    /**
     * Define a one-to-many relationship.
     *
     * @return HasMany
     */
    public function templateable();

    /**
     * @return MorphToMany
     */
    public function documentTypes();

    /**
     * @return MorphToMany
     */
    public function contents();

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
