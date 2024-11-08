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

    public function findContent(string $fullSlug)
    {
        $relations = [
            // 'documentType.fieldGroups.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
        ];

        $content = $this->searchOne(
            $fullSlug,
            fn ($q) => $q
                ->with($relations)
        );

        if ($content) {

            //todo: handle dynamic relationship
            if ($documentType = $content->documentType) {
                $documentType->setRelation('fields', $documentType->getFieldsThroughQuery()->get());
            }
        }

        return $content;
    }

    public function findPublishedContent(string $fullSlug)
    {
        $relations = [
            // 'documentType.fieldGroups.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
        ];

        $content = $this->searchOne(
            $fullSlug,
            fn ($q) => $q
                ->with($relations)
                ->whereIsPublished()
        );

        if ($content) {

            //todo: handle dynamic relationship
            if ($documentType = $content->documentType) {
                $documentType->setRelation('fields', $documentType->getFieldsThroughQuery()->get());
            }
        }

        return $content;
    }
}
