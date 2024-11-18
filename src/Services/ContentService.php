<?php

namespace SolutionForest\InspireCms\Services;

use SolutionForest\InspireCms\InspireCmsConfig;

class ContentService implements ContentServiceInterface
{
    protected string $model;

    public function __construct()
    {
        $this->model = InspireCmsConfig::getContentModelClass();
    }

    public function findPublishedContentAndView(string $fullPath, ?string $locale)
    {
        $content = $this->findPublishedContentByFullPath($fullPath);

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
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQuery()
    {
        return $this->model::query();
    }

    /**
     * @return \Laravel\Scout\Builder
     */
    protected function searchBuilder(string $keyword)
    {
        return $this->model::search($keyword);
    }
    
    /**
     * @param string $fullPath
     * @return null|\SolutionForest\InspireCms\Models\Contracts\Content|\Illuminate\Database\Eloquent\Model
     */
    protected function findPublishedContentByFullPath(string $fullPath)
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
            return $this->getQuery()
                ->with($relations)
                ->whereHas('nestableTree', fn ($query) => $query->whereIsRoot())
                ->orderBy('nestable_tree_order')
                ->first();
        }
        return $this->searchBuilder($fullPath)
            ->where('is_web', 1)
            ->where('full_path', $fullPath) // Avoid searching same slug in different parent
            ->where('__soft_deleted', 0)    // Avoid searching soft deleted content
            ->query(fn ($query) => $query
                ->with($relations)
            )
            ->first();
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
