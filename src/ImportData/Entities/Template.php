<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

/**
 * @extends BaseEntity<Template>
 */
class Template extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'content' => 'nullable|array',
    ];

    public function __construct(
        /**
         * The unique identifier for the template.
         *
         * @var string
         */
        public $slug,

        /**
         * The content of the template with theme.
         * 
         * @var array<string,string> | null
         */
        public $content = null,
    )
    {
        if (is_null($this->content)) {
            $this->content = [];
        }
    }

    public function getDataForModel(): array
    {
        return [
            'slug' => $this->slug,
            'content' => $this->content ?? [],
        ];
    }
}
