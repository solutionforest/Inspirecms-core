<?php

namespace SolutionForest\InspireCms\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\ContentRoute;
use SolutionForest\InspireCms\Models\Contracts\Language;

class LocalizedContentRoute implements Scope
{
    /**
     * @param string $urlSegment
     * @param string $defaultPattern
     * @param ?string $pattern
     */
    public function __construct(
        public $urlSegment,
        public $defaultPattern,
        public $pattern = null,
    ) { }
    
    public function apply(Builder $builder, Model $model): void
    {
        if (! $model instanceof Content) {
            return;
        }

        /**
         * @var Model & ContentRoute
         */
        $routeModel = $model->routes()->getRelated();
        /**
         * @var Model & Language
         */
        $languageModel = $routeModel->language()->getRelated();

        $routeQuery = $routeModel->query()
            ->addSelect([
                'locale' => $languageModel->query()
                    ->whereColumn('language_id', $languageModel->getQualifiedKeyName())
                    ->select('code')
                    ->limit(1)
            ]);

        if ($this->pattern && $this->pattern !== $this->defaultPattern) {
            ray($this->pattern)->orange();
            $routeQuery->where('pattern', $this->pattern);
        } else {
            $routeQuery->where('url_segment', $this->urlSegment);
        }

        $builder->getQuery()
            ->joinSub(
                $routeQuery,
                'localized_content_route',
                $model->getQualifiedKeyName(),
                '=',
                'localized_content_route.content_id'
            )
            ->select([
                'cms_content.*',
                'localized_content_route.locale as route_language_code',
            ]);
    }
}
