<?php

namespace SolutionForest\InspireCms\Services;

use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;

class PageService implements PageServiceInterface
{
    public function __construct(
        protected ContentServiceInterface $contentService
    ) { }

    public function findPublishedContentAndView(string $fullPath, ?string $locale)
    {
        $content = $this->searchPublishedContentByFullPath($fullPath);

        if (is_null($content)) {
            return [null, null];
        }

        if (! $content->isPublished() || ! $content->isWebPage()) {
            return [null, null];
        }

        $template = $content->getDefaultTemplate() ?? $content->documentType?->getDefaultTemplate();

        $lang = collect(InspireCms::getAllAvailableLanguages())->first(fn (LanguageDto $languageDto) => $languageDto->locale == $locale);

        return [$content->toDto($lang->code), $template?->getViewFullName()];
    }

    /**
     * @return null|\SolutionForest\InspireCms\Models\Contracts\Content|\Illuminate\Database\Eloquent\Model
     */
    protected function searchPublishedContentByFullPath(string $fullPath)
    {
        $relations = [
            'documentType.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
            'ancestorsAndSelf', // for url (full path)
        ];

        // ensure the format of full path
        $fullPath = $this->ensureFormatOfFullPath($fullPath);

        // if the full path is the root path, return the index page
        if (blank(trim($fullPath, '/'))) {
            $content = $this->contentService->findIndexWebPage();
        } else {
            $content = $this->contentService->searchOne(
                $fullPath, 
                fn (\Laravel\Scout\Builder $builder) => $builder
                    ->where('is_web', 1)
                    ->where('full_path', $fullPath) // Avoid searching same slug in different parent
                    ->where('__soft_deleted', 0),   // Avoid searching soft deleted content
                fn (\Illuminate\Database\Eloquent\Builder $query) => $query
            );
        }

        // dd($content, $fullPath);

        if (is_null($content)) {
            return null;
        }

        $content->loadMissing($relations);

        return $content;
    }

    /**
     * Ensures that the given full path is in the correct format.
     *
     * @param  string  $fullPath  The full path to be formatted.
     * @return string The formatted full path.
     */
    protected function ensureFormatOfFullPath(string $fullPath): string
    {
        return (string) str($fullPath)->trim()->prepend('/');
    }
}
