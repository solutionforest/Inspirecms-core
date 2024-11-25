<?php

namespace SolutionForest\InspireCms\Services;

class PageService implements PageServiceInterface
{
    public function __construct(
        protected ContentServiceInterface $contentService
    ) {}

    public function findContentAndView($fullPath, $locale)
    {
        $content = $this->findContentAndLangByFullPath($fullPath);

        if (is_null($content)) {
            return [null, null];
        }

        if (! $content->isPublished() || ! $content->isWebPage()) {
            return [null, null];
        }

        $template = $content->getDefaultTemplate() ?? $content->documentType?->getDefaultTemplate();

        return [$content->toDto($locale), $template?->getViewFullName()];
    }

    /**
     * @return null|\SolutionForest\InspireCms\Models\Contracts\Content|\Illuminate\Database\Eloquent\Model
     */
    protected function findContentAndLangByFullPath(?string $fullPath)
    {
        $relations = [
            'documentType.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
        ];

        // ensure the format of full path
        $fullPath = $this->ensureFormatOfFullPath($fullPath ?? '');

        // if the full path is the root path, return the index page
        if (blank(trim($fullPath, '/'))) {
            $content = $this->contentService->findDefaultWebPage();
        } else {
            $content = $this->contentService->findWebPageBySlugPath($fullPath);
        }

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
