<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

/**
 * @extends BaseEntity<Content>
 */
class Content extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'title' => 'required|array',
        'documentType' => 'required|string',
        'properties' => 'array',
        'publishState' => 'required|string',
        'sitemap' => 'nullable|array',
        'webSetting' => 'nullable|array',
        'parent' => 'nullable|string',
        'template' => 'nullable|string',
    ];

    public function __construct(
        /**
         * The unique identifier for the content.
         *
         * @var string
         */
        public $slug,
        /**
         * The title of the content.
         *
         * @var array<string,string>
         */
        public $title,
        /**
         * The slug of the document type.
         *
         * @var string
         */
        public $documentType,
        /**
         * An array of properties associated with the content.
         *
         * @var array<string,mixed>
         */
        public $properties = [],
        /**
         * The publish state of the content.
         *
         * @var string
         */
        public $publishState = 'draft',
        /**
         * The sitemap settings.
         *
         * @var array
         */
        public $sitemap = [],
        /**
         * The web settings.
         *
         * @var array
         */
        public $webSetting = [],
        /**
         * The parent content's slug path. Null if no parent.
         *
         * @var string|null
         */
        public $parent = null,
        /**
         * The template identifier. Optional.
         *
         * @var string|null
         */
        public $template = null,
    ) {}

    public function getDataForModel(): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'status' => inspirecms_content_statuses()->getOption($this->publishState)?->getValue(),
        ];
    }

    public function getSitemapData(): array
    {
        return array_merge([
            'priority' => 0.5,
            'change_frequency' => 'monthly',
            'enable' => true,
        ], $this->sitemap ?? []);
    }

    public function getWebSettingData(): array
    {
        return array_merge([
            'seo' => [
                'meta_title' => $this->title,
                'meta_description' => [],
                'meta_keywords' => [],
                'og_title' => $this->title,
                'og_description' => [],
                'og_image' => [],
            ],
            'robots' => [
                'index' => true,
                'follow' => true,
            ],
            'redirect_path' => null,
            'redirect_content_id' => KeyHelper::generateMinUuid(),
            'redirect_type' => null,
        ], $this->webSetting ?? []);
    }
}
