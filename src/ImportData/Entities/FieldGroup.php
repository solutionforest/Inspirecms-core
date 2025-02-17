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
    ) {}

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

    public function toExportArray(): array
    {
        $arrayOrder = ['slug', 'title', 'fields'];

        $list = parent::toArray();
        $list['fields'] = collect($this->fields ?? [])
            ->map(fn ($field) => $field->toExportArray())
            ->toArray();

        return collect($list)
            ->only($arrayOrder)
            ->sortBy(fn ($value, $key) => array_search($key, $arrayOrder))
            ->all();
    }
}
