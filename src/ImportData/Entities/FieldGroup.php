<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup as ContractsFieldGroup;

/**
 * @extends BaseEntity<FieldGroup>
 */
class FieldGroup extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'title' => 'nullable|string',
        'fields' => 'array',
    ];

    protected static array $propertiesOrder = [
        'slug',
        'title',
        'fields',
    ];

    protected static array $limitedProperties = [
        'slug',
        'title',
        'fields',
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

        /**
         * The fields associated with the field group (optional).
         *
         * @var Field[]
         */
        public $fields = [],
    ) {
        $this->initialize();
    }

    public function getDataForModel(): array
    {
        return [
            'name' => $this->slug,
            'title' => $this->title ?? (string) str($this->slug)->title()->replace('_', ' '),
        ];
    }

    /**
     * @param  ContractsFieldGroup|Model  $record
     */
    public static function fromRecord($record)
    {
        $data = Arr::only($record->toArray(), ['title']);
        $data['slug'] = $record->name;
        $data['fields'] = $record->fields
            ->map(fn ($field) => Field::fromRecord($field))
            ->toArray();

        return static::fromArray($data);
    }
}
