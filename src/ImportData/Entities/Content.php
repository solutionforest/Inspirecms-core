<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Models\Contracts\Content as ContractsContent;
use SolutionForest\InspireCms\Models\Contracts\ContentRoute;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

/**
 * @extends BaseEntity<Content>
 */
class Content extends BaseEntity
{
    protected static array $rules = [
        'slug' => 'required|string',
        'title' => 'required|array',
        'documentType' => 'required|string',
        'isDefault' => 'nullable|boolean',
        'properties' => 'array',
        'publishState' => 'required|string',
        'sitemap' => 'nullable|array',
        'webSetting' => 'nullable|array',
        'routes' => 'nullable|array',
        'parent' => 'nullable|string',
        'template' => 'nullable|string',
    ];

    protected static array $propertiesOrder = [
        'slug',
        'title',
        'parent',
        'documentType',
        'isDefault',
        'properties',
        'publishState',
        'sitemap',
        'webSetting',
        'template',
        'routes',
    ];

    protected static array $limitedProperties = [
        'slug',
        'title',
        'parent',
        'documentType',
        'isDefault',
        'properties',
        'publishState',
        'sitemap',
        'webSetting',
        'template',
        'routes',
    ];

    public function __construct(
        /**
         * The unique identifier for the content.
         *
         * @var string
         */
        public $slug,
        /**
         * The title of the content.
         *
         * @var array<string,string>
         */
        public $title,
        /**
         * The slug of the document type.
         *
         * @var string
         */
        public $documentType,
        /**
         * Indicates whether this content is the default.
         *
         * @var bool
         */
        public $isDefault = false,
        /**
         * An array of properties associated with the content.
         *
         * @var array<string,mixed>
         */
        public $properties = [],
        /**
         * The publish state of the content.
         *
         * @var string
         */
        public $publishState = 'draft',
        /**
         * The sitemap settings.
         *
         * @var array
         */
        public $sitemap = [],
        /**
         * The web settings.
         *
         * @var array
         */
        public $webSetting = [],
        /**
         * The routes for the content.
         *
         * @var array
         */
        public $routes = [],
        /**
         * The parent content's slug path. Null if no parent.
         *
         * @var string|null
         */
        public $parent = null,
        /**
         * The template identifier. Optional.
         *
         * @var string|null
         */
        public $template = null,
    ) {
        $this->initialize();
    }

    protected function initialize(): void
    {
        // Set the default values
        $this->isDefault ??= false;
        $this->publishState ??= 'draft';
    }

    public function getDataForModel(): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'status' => inspirecms_content_statuses()->getOption($this->publishState)?->getValue(),
            'is_default' => $this->isDefault ?? false,
        ];
    }

    public function getSitemapData(): array
    {
        return array_merge([
            'priority' => 0.5,
            'change_frequency' => 'monthly',
            'enable' => true,
        ], $this->sitemap ?? []);
    }

    public function getWebSettingData(): array
    {
        return array_merge([
            'seo' => [
                'meta_title' => $this->title,
                'meta_description' => [],
                'meta_keywords' => [],
                'og_title' => $this->title,
                'og_description' => [],
                'og_image' => [],
            ],
            'robots' => [
                'index' => true,
                'follow' => true,
            ],
            'redirect_path' => null,
            'redirect_content_id' => KeyHelper::generateMinUuid(),
            'redirect_type' => null,
        ], $this->webSetting ?? []);
    }

    public function getRoutesData(): array
    {
        return collect($this->routes ?? [])
            ->map(fn (array $route) => array_merge([
                'language_id' => null,
                'is_default_pattern' => false,
                'regex_constraints' => [],
            ], $route))
            ->all();
    }

    /**
     * @param  ContractsContent|Model  $record
     */
    public static function fromRecord($record)
    {
        $data = $record->toArray();

        if (($defaultTemplate = $record->getDefaultTemplate())) {
            $data['template'] = $defaultTemplate->slug;
        }
        $data['documentType'] = $record->documentType?->slug;
        $data['isDefault'] = $record->is_default;

        $data['properties'] = $record->getLatestPublishedPropertyData();
        $data['publishState'] = $record->display_status?->getName();

        $data['sitemap'] = Arr::only($record->sitemap?->toArray() ?? [], [
            'priority',
            'change_frequency',
            'enable',
        ]);
        $data['webSetting'] = Arr::only($record->webSetting?->toArray() ?? [], [
            'seo',
            'robots',
            'redirect_path',
            'redirect_content_id',
            'redirect_type',
        ]);
        if (! ($record->documentType?->isDataType() ?? false)) {

            $data['routes'] = collect($record->routes)
                ->map(fn (ContentRoute | Model $route) => array_merge([
                    'locale' => $route->language?->code,
                ], Arr::only($route->toArray(), [
                    'uri',
                    'is_default_pattern',
                    'regex_constraints',
                ])))
                ->all();
        }

        // full path
        $data['parent'] = $record->parent?->path?->value;

        return static::fromArray(Arr::only($data, static::$limitedProperties));
    }
}
