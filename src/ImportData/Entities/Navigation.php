<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\Navigation as ContractsNavigation;

/**
 * @extends BaseEntity<Navigation>
 */
class Navigation extends BaseEntity
{
    protected static array $rules = [
        'category' => 'required|string',
        'type' => 'required|string',
        'title' => 'required|array',
        'title.*' => 'string',
        'url' => 'nullable|array',
        'url.*' => 'nullable|string',
        'target' => 'nullable|string',
        'contentSlugPath' => 'nullable|string',
        'children' => 'array',
    ];

    protected static array $propertiesOrder = [
        'id',
        'category',
        'type',
        'title',
        'contentSlugPath',
        'url',
        'target',
        'children',
    ];

    protected static array $limitedProperties = [
        'id',
        'category',
        'type',
        'title',
        'url',
        'target',
        'contentSlugPath',
        'children',
    ];

    public function __construct(
        /**
         * The category of the navigation item. (e.g. main, footer, etc.)
         *
         * @var string
         */
        public $category,
        /**
         * The type of the navigation item. (e.g. link, content, group, etc.)
         *
         * @var string
         */
        public $type,
        /**
         * The title of the navigation item.
         *
         * @var array<string,string>
         */
        public $title,
        /**
         * The URL of the navigation item (optional).
         *
         * @var ?array<string,string>
         */
        public $url = null,
        /**
         * The target of the navigation item (optional). (e.g. _blank, _self, etc.)
         *
         * @var string|null
         */
        public $target = null,
        /**
         * @var string|int|null $id The identifier for the navigation entity.  Update existing if provided.
         */
        public $id = null,
        /**
         * The content slug path of the navigation item (optional).
         *
         * @var string|null
         */
        public $contentSlugPath = null,
        /**
         * The children of the navigation item.
         *
         * @var Navigation[]
         */
        public array $children = [],
    ) {
        $this->initialize();
    }

    public static function fromArray(array $parameters)
    {
        $parameters['children'] = collect($parameters['children'] ?? [])->map(fn ($child) => is_array($child) ? static::fromArray($child) : $child)->toArray();

        return parent::fromArray($parameters);
    }

    public function getDataForModel(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'type' => $this->type,
            'title' => $this->title,
            'url' => $this->url,
            'target' => $this->target,
        ];
    }

    /**
     * @param  ContractsNavigation|Model  $record
     */
    public static function fromRecord($record)
    {
        $data = collect($record->toArray())->only(static::$limitedProperties)->except('children')->all();

        $data['contentSlugPath'] = $record->content?->path?->value;

        $children = [];

        foreach ($record->children as $item) {

            $children[] = static::fromRecord($item);

        }

        $data['children'] = $children;

        return static::fromArray($data);
    }
}
