<?php

namespace SolutionForest\InspireCms\Services;

interface PageServiceInterface
{
    /**
     * Finds a publish content and its template by its full path.
     *
     * @param  string  $fullPath  The full path of the page to find.
     * @param  ?string  $locale  The locale of the page to find.
     * @return array{0:?ContentDto,1:?string} The found content and its view.
     */
    public function findPublishedContentAndView(string $fullPath, ?string $locale);
}
