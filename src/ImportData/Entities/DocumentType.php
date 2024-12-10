<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

/**
 * @extends BaseEntity<DocumentType>
 */
class DocumentType extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'showAsTable' => 'nullable|boolean',
        'category' => 'required|string',
        'icon' => 'nullable|string',
        'title' => 'nullable|string',
        'fieldGroups' => 'array',
        'templates' => 'array',
        'defaultTemplate' => 'nullable|string',
        'inheritance' => 'array',
        'rejected' => 'array',
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
        public $showAsTable,
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
         * @var array $rejected An array to hold rejected document types (optional).
         */
        public $rejected = [],
    ) {}

    /** {@inheritDoc} */
    public static function fromArray(array $parameters)
    {
        if (blank($parameters['category'] ?? null) || ! isset($parameters['category'])) {
            $parameters['category'] = 'web';
        }
        $arrayFields = ['fieldGroups', 'templates', 'inheritance', 'rejected'];
        foreach ($arrayFields as $field) {
            if (! isset($parameters[$field])) {
                $parameters[$field] = [];
            }
        }

        return parent::fromArray($parameters);
    }

    public function getDataForModel(): array
    {
        return [
            'show_as_table' => $this->showAsTable ?? false,
            'category' => $this->category,
            'title' => $this->title ?? (string) str($this->slug)->title()->replace('_', ' '),
            'slug' => $this->slug,
            'icon' => $this->icon,
        ];
    }
}
