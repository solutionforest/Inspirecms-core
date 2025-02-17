<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use SolutionForest\InspireCms\Models\Contracts\Template as ContractsTemplate;

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
    ) {
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

    /**
     * @param ContractsTemplate|Model $record
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
