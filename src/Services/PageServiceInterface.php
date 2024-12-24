<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Dtos\TemplateDto;

/**
 * Interface PageServiceInterface
 *
 * Implementations of this interface are responsible for published content.
 */
interface PageServiceInterface
{
    /**
     * Finds a published content and its template by its full path.
     *
     * @param  ?string  $fullPath  The full path of the page to find.
     * @param  string  $locale  The locale of the page to find.
     * @return array{0:?ContentDto,1:?TemplateDto} The found content and its template.
     */
    public function findContentAndTemplate($fullPath, $locale);

    /**
     * Find published content by real path.
     *
     * @param  string  $slugPath  The slug path of the content.
     * @param  string  $locale  The locale of the content.
     * @return Collection<ContentDto> The published content found under the given slug path and locale.
     */
    public function findContentByRealPath($slugPath, $locale);

    /**
     * Retrieve the published content under a given real path for a specific locale.
     *
     * @param  string  $slugPath  The slug path to search for content.
     * @param  string  $locale  The locale for which the content is to be retrieved.
     * @param  int|null  $limit  The maximum number of content items to retrieve, or null for unlimited.
     * @return Collection<ContentDto> The content found under the given slug path and locale.
     */
    public function getContentUnderRealPath($slugPath, $locale, $limit = null);
}
