<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

/**
 * @extends BaseEntity<Template>
 */
class Template extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'content' => 'nullable|string',
    ];

    public function __construct(
        /**
         * The unique identifier for the template.
         *
         * @var string
         */
        public $slug,
        /**
         * The content of the template. Default is null.
         *
         * @var string|null
         */
        public $content = null
    ) {}

    public function getDataForModel(): array
    {
        return [
            'slug' => $this->slug,
        ];
    }
}
