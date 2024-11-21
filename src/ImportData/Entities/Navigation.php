<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

/**
 * @extends BaseEntity<Navigation>
 */
class Navigation extends BaseEntity
{
    protected static array $rules = [
        'category' => 'required|string',
        'type' => 'required|string',
        'title' => 'required|array',
        'title.*' => 'string',
        'url' => 'nullable|string',
        'target' => 'nullable|string',
        'contentSlugPath' => 'nullable|string',
        'children' => 'array',
    ];

    public function __construct(
        /**
         * The category of the navigation item. (e.g. main, footer, etc.)
         *
         * @var string
         */
        public $category,
        /**
         * The type of the navigation item. (e.g. link, content, group, etc.)
         *
         * @var string
         */
        public $type,
        /**
         * The title of the navigation item.
         *
         * @var array<string,string>
         */
        public $title,
        /**
         * The URL of the navigation item (optional).
         *
         * @var string|null
         */
        public $url = null,
        /**
         * The target of the navigation item (optional). (e.g. _blank, _self, etc.)
         *
         * @var string|null
         */
        public $target = null,
        /**
         * The content slug path of the navigation item (optional).
         *
         * @var string|null
         */
        public $contentSlugPath = null,
        /**
         * The children of the navigation item.
         *
         * @var Navigation[]
         */
        public array $children = [],
    ) {}

    public function getDataForModel(): array
    {
        return [
            'category' => $this->category,
            'type' => $this->type,
            'title' => $this->title,
            'url' => $this->url,
            'target' => $this->target,
        ];
    }
}
