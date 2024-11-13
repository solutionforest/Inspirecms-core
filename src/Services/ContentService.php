<?php

namespace SolutionForest\InspireCms\Services;

use SolutionForest\InspireCms\InspireCmsConfig;

class ContentService extends IndexSearchService implements ContentServiceInterface
{
    public function __construct()
    {
        parent::__construct(InspireCmsConfig::getContentModelClass());
    }

    public function findById(int $id)
    {
        // Implement the logic to find content by ID
        return $this->getQuery()->find($id);
    }

    public function findBySlug(string $slug)
    {
        // Implement the logic to find content by slug
        return $this->getQuery()->where('slug', $slug)->first();
    }

    public function findPublishedContentByFullPath(string $fullPath)
    {
        $relations = [
            'documentType.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
            'descendantsAndSelf', // for url (full path)
        ];

        // ensure the format of full path 
        $fullPath = $this->ensureFormatOfFullPath($fullPath);

        $content = $this->searchOne(
            $fullPath,
            fn ($s) => $s
                ->where('is_web', 1)
                ->where('full_path', $fullPath)
                ,
            fn ($q) => $q
                ->with($relations)
                ->whereIsPublished()
        );

        return $content;
    }

    /**
     * Ensures that the given full path is in the correct format.
     *
     * @param string $fullPath The full path to be formatted.
     * @return string The formatted full path.
     */
    protected function ensureFormatOfFullPath(string $fullPath): string
    {
        return (string) str($fullPath)
            ->trim()
            ->prepend('/');
    }
}
