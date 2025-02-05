<?php

namespace SolutionForest\InspireCms\Content;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\ContentRoute;

class DefaultSegmentProvider implements SegmentProviderInterface
{
    public function getUrlSegment($content, $locale = null)
    {
        $content->loadMissing(['routes.language']);

        $locale = $this->resolveLocale($locale);

        /**
         * @var ?ContentRoute
         */
        $defaultRoute = $content->routes->first(fn (ContentRoute $c) => $c->language_id === null);
        /**
         * @var ?ContentRoute
         */
        $targetRoute = $content->routes->first(fn (ContentRoute $c) => $c->language?->code === $locale);

        // fallback to default locale
        if (is_null($targetRoute) || $this->isDefaultLocale($locale)) {
            $targetRoute = $defaultRoute;
        }

        if ($targetRoute) {

            if ($targetRoute->is_default_pattern) {
                return str($this->getDefaultRoutePattern())
                    ->replace(
                        ['{locale?}', '{locale}'],
                        $locale != $this->getDefaultLocale() ? (trim($locale, '/')) : ''
                    )
                    ->replace(['{slug?}', '{slug}'], trim($targetRoute->uri, '/'))
                    ->toString();
            } else {

                return $targetRoute->uri;
            }
        }

        return null;
    }

    public function getSegment($content)
    {
        $source = $this->getSourceSegment($content);

        $slugs = collect($source)->filter(function ($item) {
            return ! $item['default'];
        })->pluck('slug')->values()->all();

        return $this->ensureSegmentFormat($slugs);
    }

    public function getSegments($content)
    {
        $source = $this->getSourceSegment($content);

        $slugs = Arr::pluck($source, 'slug');

        return $slugs;
    }

    public function getPath($content)
    {
        return $this->ensurePathFormat($this->getSegments($content));
    }

    public function getDefaultRoutePattern()
    {
        return '{locale?}/{slug?}';
    }

    public function getDefaultRouteConstraints()
    {
        return [
            'slug' => '.*',
        ];
    }

    public function getUrlSegmentFromDefaultRoute($route)
    {
        $routeParameters = $route->parameters();

        $slug = $routeParameters['slug'] ?? null;
        $locale = $routeParameters['locale'] ?? null;

        // e.g. /case-studies/case-study-1
        if ($locale && ! in_array($locale, $this->getAvailabledLocales())) {
            $slug = str($slug)->explode('/')->filter()->prepend($locale)->implode('/');
        }

        if ($locale && ! $slug) {
            if ($locale === $this->getLocaleFromDefaultRoute($route)) {
                $slug = null;
            } else {
                $slug = $locale;
            }
        }

        return $this->ensureSegmentFormat(array_filter([$slug]));
    }

    public function getLocaleFromDefaultRoute($route)
    {
        $routeParameters = $route->parameters();

        $locale = $routeParameters['locale'] ?? null;

        if (! in_array($locale, $this->getAvailabledLocales())) {
            $locale = $this->getDefaultLocale();
        }

        $locale ??= $this->getDefaultLocale();

        return $locale;
    }

    protected function getSourceSegment(Content $content): array
    {
        $ancestorsAndSelf = collect($content->ancestorsAndSelf)->reverse()->values();

        $segments = [];

        foreach ($ancestorsAndSelf as $index => $item) {

            $segments[] = [
                'default' => $item->is_default,
                'slug' => $item->slug,
            ];
        }

        return $segments;
    }

    protected function ensureSegmentFormat(array $slugs): string
    {
        return Str::of(collect($slugs)->filter()->implode('/'))
            ->trim('/')
            ->prepend('/')
            ->toString();
    }

    protected function ensurePathFormat(array $slugs): string
    {
        return Str::of(collect($slugs)->filter()->implode('/'))
            ->trim('/')
            ->toString();
    }

    /**
     * @param  null|string|LanguageDto  $locale
     */
    protected function resolveLocale($locale): ?string
    {
        if ($locale instanceof LanguageDto) {
            $locale = $locale->code;
        }

        return $locale ?? $this->getDefaultLocale();
    }

    protected function isDefaultLocale($locale): bool
    {
        return $locale === $this->getDefaultLocale();
    }

    protected function getDefaultLocale(): string
    {
        return InspireCms::getFallbackLanguage()?->code ?? app()->getLocale();
    }

    protected function getAvailabledLocales(): array
    {
        return array_keys(InspireCms::getAllAvailableLanguages());
    }
}
