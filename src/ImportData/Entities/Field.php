<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

/**
 * @extends BaseEntity<Field>
 */
class Field extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'type' => 'required|string',
        'config' => 'array',
        'label' => 'nullable|string',
    ];

    public function __construct(
        /**
         * The slug of the field.
         *
         * @var string
         */
        public $slug,
        /**
         * The type of the field.
         *
         * @var string
         */
        public $type,
        /**
         * The configuration of the field.
         *
         * @var array<string,mixed>
         */
        public $config = [],
        /**
         * The label of the field (optional).
         *
         * @var string|null
         */
        public $label = null,
    ) { }

    public function getDataForModel(): array
    {
        return [
            'name' => $this->slug,
            'type' => $this->type,
            'config' => $this->config,
            'label' => $this->label ?? (string) str($this->slug)->title()->replace('_', ' '),
        ];
    }
}
