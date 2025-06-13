<?php

namespace SolutionForest\InspireCms\Resolvers;

use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Content\SegmentProviderInterface;
use SolutionForest\InspireCms\Dtos\PublishedContentDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Services\ContentServiceInterface;

class PublishedContentResolver implements PublishedContentResolverInterface
{
    protected ContentServiceInterface $contentService;

    protected SegmentProviderInterface $segmentProvider;

    public function __construct(ContentServiceInterface $contentService)
    {
        $this->contentService = $contentService;
        $this->segmentProvider = ContentSegmentFactory::create();
    }

    public function resolve(...$args)
    {
        /**
         * @var null | \Illuminate\Http\Request $request
         */
        $request = $args[0] ?? request() ?? null;

        if (is_null($request)) {
            return null;
        }

        $requestRoute = $request->route();
        if (is_null($requestRoute)) {
            return null;
        }

        [$content, $locale] = $this->getContentAndLocaleByRoute($requestRoute);

        if (is_null($content)) {
            return null;
        }
        if (! $content->isPublished() || ! $content->isWebPage()) {
            return null;
        }

        $content->loadMissing(static::getDtoRelations());

        $contentDto = $content->toDto($locale);
        $templateDto = $this->getDefaultTemplateForContent($content);

        return PublishedContentDto::fromArray([
            'content' => $contentDto,
            'template' => $templateDto,
            'locale' => $locale,
            'parameters' => Arr::except($requestRoute->parameters(), 'locale'),
        ]);
    }

    protected function getContentAndLocaleByRoute($route)
    {
        $routePatternToCheck = $route->uri();

        if ($routePatternToCheck == $this->segmentProvider->getDefaultRoutePattern()) {
            $urlSegmentToFind = $this->segmentProvider->getUrlSegmentFromDefaultRoute($route);

            [$content, $landId] = $this->contentService->findByRoutePatternWithLangId(
                uri: $urlSegmentToFind,
                isDefaultRoutePattern: true,
                isWebPage: true,
                sorting: [
                    '__latest_version_publish_dt' => 'desc',
                ],
            )->map(fn ($arr) => [$arr['content'], $arr['language_id']])->first();

            // Check locale from the route if not set lang for the content's route
            if (is_null($landId)) {
                $locale = $this->segmentProvider->getLocaleFromDefaultRoute($route);
            }
        } else {

            [$content, $landId] = $this->contentService->findByRoutePatternWithLangId(
                uri: $routePatternToCheck,
                isDefaultRoutePattern: false,
                isWebPage: true,
                sorting: [
                    '__latest_version_publish_dt' => 'desc',
                ],
            )->map(fn ($arr) => [$arr['content'], $arr['language_id']])->first();
        }

        if (! isset($locale)) {
            $locale = (($landId !== null) ? $this->getLocaleById($landId) : null) ?? $this->getDefualtLocale();
        }

        return [$content, $locale];
    }

    protected function getLocaleById($id)
    {
        return collect(InspireCms::getAllAvailableLanguages())
            ->first(fn ($lang) => $lang->id == $id)
            ?->code;
    }

    protected function getDefualtLocale()
    {
        return InspireCms::getFallbackLanguage()?->code ?? app()->getLocale();
    }

    /**
     * @param  null|\SolutionForest\InspireCms\Models\Contracts\Content & \Illuminate\Database\Eloquent\Model  $content
     * @return null|\SolutionForest\InspireCms\Dtos\TemplateDto
     */
    protected function getDefaultTemplateForContent($content)
    {
        $template = $this->contentService->getDefaultTemplateFor($content);

        $theme = inspirecms_templates()->getCurrentTheme();

        return $template?->toDto($theme);
    }

    protected static function getDtoRelations(): array
    {
        return [
            'documentType.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
        ];
    }
}
