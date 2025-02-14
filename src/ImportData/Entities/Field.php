<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Models\Contracts\Field as ContractsField;


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
    ) {}

    public function getDataForModel(): array
    {
        return [
            'name' => $this->slug,
            'type' => $this->type,
            'config' => $this->config,
            'label' => $this->label ?? (string) str($this->slug)->title()->replace('_', ' '),
        ];
    }

    /**
     * @param ContractsField|Model $record
     */
    public static function fromRecord($record)
    {
        $data = Arr::only($record->toArray(), ['config', 'type', 'label']);
        $data['slug'] = $record->name;
        return static::fromArray($data);
    }

    public function toExportArray(): array
    {
        $arrayOrder = ['slug', 'type', 'config', 'label'];

        return collect(parent::toArray())
            ->only($arrayOrder)
            ->sortBy(fn ($value, $key) => array_search($key, $arrayOrder))
            ->all();
    }
}
