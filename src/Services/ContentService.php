<?php

namespace SolutionForest\InspireCms\Services;

use SolutionForest\InspireCms\Support\InspireCmsConfig;

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

    public function findPage(string $fullSlug)
    {
        $content = $this->searchOne($fullSlug);

        if ($content) {

            $content->loadMissing([
                'documentType.fieldGroups.fields',
                'documentType.templates',
                'webSetting',
                'publishedVersions',
                'templates',
            ]);
        }

        return $content;
    }
}
