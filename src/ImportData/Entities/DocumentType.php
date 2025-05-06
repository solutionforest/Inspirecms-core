<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Models\Contracts\DocumentType as ContractsDocumentType;

/**
 * @extends BaseEntity<DocumentType>
 */
class DocumentType extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'showAsTable' => 'nullable|boolean',
        'showAtRoot' => 'nullable|boolean',
        'category' => 'required|string',
        'icon' => 'nullable|string',
        'title' => 'nullable|string',
        'fieldGroups' => 'array',
        'templates' => 'array',
        'defaultTemplate' => 'nullable|string',
        'inheritance' => 'array',
        'allowed' => 'array',
    ];

    protected static array $propertiesOrder = [
        'slug',
        'title',
        'showAsTable',
        'showAtRoot',
        'category',
        'icon',
        'templates',
        'defaultTemplate',
        'fieldGroups',
        'inheritance',
        'allowed',
    ];

    protected static array $limitedProperties = [
        'slug',
        'title',
        'showAsTable',
        'showAtRoot',
        'category',
        'icon',
        'templates',
        'defaultTemplate',
        'fieldGroups',
        // 'inheritance', @todo Hide this for now
        'allowed',
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
         * Whether the document type should be shown at root while creating a new content.
         *
         * @var bool
         */
        public $showAtRoot,

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
         * @var string[] $allowed An array to hold allowed document types (optional).
         */
        public $allowed = [],
    ) {
        $this->initialize();
    }

    protected function initialize(): void
    {
        // Set the default values
        $this->showAsTable ??= false;
        $this->showAtRoot ??= true;
        if (blank($this->category) || ! isset($this->category)) {
            $this->category = 'web';
        }
    }

    public function getDataForModel(): array
    {
        return [
            'show_as_table' => $this->showAsTable ?? false,
            'show_at_root' => $this->showAtRoot ?? true,
            'category' => $this->category,
            'title' => $this->title ?? (string) str($this->slug)->title()->replace(['_', '-'], ' '),
            'slug' => $this->slug,
            'icon' => $this->icon,
        ];
    }

    /**
     * @param  ContractsDocumentType|Model  $record
     */
    public static function fromRecord($record)
    {
        $data = $record->toArray();

        if (($defaultTemplate = $record->getDefaultTemplate())) {
            $data['defaultTemplate'] = $defaultTemplate->slug;
        }
        $data['templates'] = $record->templates->pluck('slug')->toArray();
        $data['allowed'] = $record->allowedDocumentTypes->pluck('slug')->toArray();
        $data['fieldGroups'] = $record->fieldGroups->pluck('name')->toArray();

        $data['showAsTable'] = $record->show_as_table ?? false;
        $data['showAtRoot'] = $record->show_at_root ?? true;
        $data['category'] = $record->category;

        return static::fromArray(Arr::only($data, static::$limitedProperties));
    }
}
