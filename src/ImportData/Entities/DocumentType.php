<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

/**
 * @extends BaseEntity<DocumentType>
 */
class DocumentType extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'childrenAsTable' => 'required|boolean',
        'category' => 'required|string',
        'icon' => 'nullable|string',
        'title' => 'nullable|string',
        'fieldGroups' => 'array',
        'templates' => 'array',
        'defaultTemplate' => 'nullable|string',
        'inheritance' => 'array',
        'parent' => 'nullable|string',
    ];

    public function __construct(
        /**
         * The slug of the document type.
         *
         * @var string
         */
        public $slug,
        /**
         * Whether the children should be displayed as a table.
         *
         * @var bool
         */
        public $childrenAsTable,
        /**
         * The category of the document type. (e.g. web, inheritance, etc.)
         *
         * @var string
         */
        public $category,
        /**
         * @var mixed|null $icon The icon associated with the document type. Default is null.
         */
        public $icon = null,
        /**
         * The title of the document type (optional).
         *
         * @var string|null
         */
        public $title = null,
        /**
         * The field groups associated with the document type (optional).
         *
         * @var string[]
         */
        public $fieldGroups = [],
        /**
         * The templates associated with the document type (optional).
         *
         * @var string[]
         */
        public $templates = [],
        /**
         * The default template (optional).
         *
         * @var string|null
         */
        public $defaultTemplate = null,
        /**
         * The document types from which this document type inherits (optional).
         *
         * @var string[]
         */
        public $inheritance = [],
        /**
         * The parent document type (optional).
         *
         * @var string|null
         */
        public $parent = null,
    ) { }

    /** @inheritDoc */
    public static function fromArray(array $parameters)
    {
        if (blank($parameters['category'] ?? null) || !isset($parameters['category'])) {
            $parameters['category'] = 'web';
        }
        if (!isset($parameters['inheritance'])) {
            $parameters['inheritance'] = [];
        }

        return parent::fromArray($parameters);
    }

    public function getDataForModel(): array
    {
        return [
            'show_children_as_table' => $this->childrenAsTable,
            'category' => $this->category,
            'title' => $this->title ?? (string) str($this->slug)->title()->replace('_', ' '),
            'slug' => $this->slug,
            'icon' => $this->icon,
        ];
    }
}
