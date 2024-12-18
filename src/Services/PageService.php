<?php

namespace SolutionForest\InspireCms\Services;

class PageService implements PageServiceInterface
{
    public function __construct(
        protected ContentServiceInterface $contentService
    ) {}

    /** {@inheritDoc} */
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

    /** {@inheritDoc} */
    public function findContentByRealPath($slugPath, $locale)
    {
        $content = $this->contentService->findByRealPath($slugPath, static::getDtoRelations());

        if (is_null($content) || ! $content->isPublished()) {
            return null;
        }

        $content->loadMissing(static::getDtoRelations());

        return $content->toDto($locale);
    }

    /** {@inheritDoc} */
    public function getContentUnderRealPath($slugPath, $locale, $limit = null)
    {
        $content = $this->contentService->getUnderRealPath($slugPath, $limit, static::getDtoRelations());

        $items = $content
            ->filter(fn ($item) => $item->isPublished())
            ->map(fn ($item) => $item->toDto($locale));

        return new \SolutionForest\InspireCms\Collection\ContentDtoCollection($items);
    }

    //region Helpers
    /**
     * @return null|\SolutionForest\InspireCms\Models\Contracts\Content|\Illuminate\Database\Eloquent\Model
     */
    protected function findContentAndLangByFullPath(?string $fullPath)
    {
        $relations = static::getDtoRelations();

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

    protected static function getDtoRelations(): array
    {
        return [
            'documentType.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
        ];
    }
    //endregion Helpers
}
