<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

/**
 * @extends BaseEntity<FieldGroup>
 */
class FieldGroup extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'title' => 'nullable|string',
    ];

    public function __construct(
        /**
         * The name of the field group.
         *
         * @var string
         */
        public $slug,
        /**
         * The title of the field group (optional).
         *
         * @var string|null
         */
        public $title = null,
    ) {}

    public function getDataForModel(): array
    {
        return [
            'name' => $this->slug,
            'title' => $this->title ?? (string) str($this->slug)->title()->replace('_', ' '),
        ];
    }
}
