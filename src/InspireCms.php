<?php

namespace SolutionForest\InspireCms;

use Composer\InstalledVersions;
use Filament\Facades\Filament;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Dtos\NavigationDto;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\UrlHelper;
use SolutionForest\InspireCms\Http\Controllers as CmsControllers;
use SolutionForest\InspireCms\Http\Middleware as CmsMiddlewares;
use SolutionForest\InspireCms\Models\Contracts\Language;

class InspireCms
{
    const CORE_SLUG = 'inspirecms';

    const PACKAGE = 'solution-forest/inspirecms-core';

    protected CacheManager $cacheManager;

    /**
     * @var Collection<string,ClusterSection>
     */
    protected Collection $sections;

    protected ?array $cachedLanguages = null;

    protected ?array $cachedNavigation = null;

    protected ?array $cachedContentRoutes = null;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;

        $this->sections = collect(InspireCmsConfig::getFilamentClusters())->map(fn ($fqcn, $name) => new ClusterSection($name, $fqcn));
    }

    public static function version(): ?string
    {
        return InstalledVersions::getPrettyVersion(static::PACKAGE);
    }

    /**
     * Determine if there is a need to go to the install page
     */
    public function needInstall(): bool
    {
        /** @var ?EloquentUserProvider $provider */
        $provider = auth(AuthHelper::guardName())?->getProvider();

        if (! $provider) {
            throw new \Exception('Authentication provider not found for guard: ' . $guard);
        }
        if ($provider->getModel()::count() <= 0) {
            return true;
        }

        return false;
    }

    public function getInstallUrl(): ?string
    {
        return Filament::getPanel(InspireCmsConfig::getPanelId())?->getRegistrationUrl();
    }

    public function getImportDataUrl(): ?string
    {
        try {

            $page = InspireCmsConfig::getFilamentPage('export', \SolutionForest\InspireCms\Filament\Pages\Export::class);

            $panel = Filament::getPanel(InspireCmsConfig::getPanelId());

            $parameters['redirectUrl'] = $panel?->getHomeUrl();

            return UrlHelper::attemptToGetUrlFromPanel($page, $parameters);

        } catch (\Exception $th) {
            //
        }

        return null;
    }

    /**
     * @param  string  ...$names
     * @return \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection>
     */
    public function getSections(...$names)
    {
        $sections = $this->sections;

        $namesToFilter = collect($names)->flatten()->reduce(function ($array, $name) {
            if (empty($name)) {
                return $array;
            }

            $array[$name] = $name;

            return $array;

        }, []);

        if (count($namesToFilter) > 0) {
            $sections = $sections->filter(function (ClusterSection $section) use ($namesToFilter) {
                return in_array($section->getName(), $namesToFilter);
            });
        }

        return $sections;
    }

    /**
     * Registers the routes for the Inspire CMS.
     *
     * This method is responsible for defining the routes that will be used
     * by the Inspire CMS. It should be called during the application's
     * bootstrapping process to ensure that all necessary routes are available.
     */
    public function routes(): void
    {
        Route::name('inspirecms.')
            ->group(function () {
                Route::name('sitemap')
                    ->get('sitemap.xml', CmsControllers\SitemapController::class);

                $frontendMiddlewares = InspireCmsConfig::get('frontend.routes.middleware', [
                    CmsMiddlewares\SetUpPoweredBy::class,
                ]);
                Route::name('frontend.')
                    ->middleware($frontendMiddlewares)
                    ->group(function () {

                        $factory = ContentSegmentFactory::create();
                        $customFrontendRoutes = Schema::hasTable(InspireCmsConfig::getContentRouteTableName()) && Schema::hasTable('cache')
                            ? $this->getContentRoutes()
                            : [];

                        foreach ($customFrontendRoutes as $index => $item) {
                            Route::any($item['uri'], CmsControllers\FrontendController::class)
                                ->where($item['regex_constraints'] ?? [])
                                ->name($item['alias'] ?? 'content_' . $index);
                        }

                        // default route
                        Route::any($factory->getDefaultRoutePattern(), CmsControllers\FrontendController::class)
                            ->where($factory->getDefaultRouteConstraints())
                            ->name('default');
                    });
            });
    }

    public function addSection(ClusterSection $section): void
    {
        $this->sections->put($section->getName(), $section);
    }

    /**
     * @return array<string,\SolutionForest\InspireCms\Dtos\LanguageDto>
     */
    public function getAllAvailableLanguages(): array
    {
        if (! $this->cachedLanguages) {
            $this->cachedLanguages = $this->cacheManager
                ->store(InspireCmsConfig::get('cache.languages.store'))
                ->remember(
                    InspireCmsConfig::get('cache.languages.key'),
                    InspireCmsConfig::get('cache.languages.ttl'),
                    fn () => $this->getSerializedLanguagesForCache()
                );
        }

        return collect($this->cachedLanguages['languages'] ?? [])
            ->map(fn ($arr) => array_combine($this->cachedLanguages['alias'] ?? [], $arr))
            ->map(fn ($arr) => LanguageDto::fromArray($arr))
            ->all();
    }

    public function getFallbackLanguage(): ?LanguageDto
    {
        return collect($this->getAllAvailableLanguages())->first(fn (LanguageDto $lang) => $lang->isDefault == true);
    }

    public function forgetCachedLanguages(): void
    {
        $this->cacheManager
            ->store(InspireCmsConfig::get('cache.languages.store'))
            ->forget(InspireCmsConfig::get('cache.languages.key'));
    }

    /**
     * @return NavigationDto[]
     */
    public function getNavigation(string $category, ?string $locale = null): array
    {
        if (! $this->cachedNavigation) {
            $this->cachedNavigation = $this->cacheManager
                ->store(InspireCmsConfig::get('cache.navigation.store'))
                ->remember(
                    InspireCmsConfig::get('cache.navigation.key'),
                    InspireCmsConfig::get('cache.navigation.ttl'),
                    fn () => $this->getSerializedNavigationForCache()
                );
        }

        return collect($this->processNavigationData($this->cachedNavigation['navigation'] ?? [], $this->cachedNavigation['alias'] ?? []))
            ->where('category', $category)
            ->where('isActive', true)
            ->map(fn ($arr) => NavigationDto::fromTranslatableArray($arr, $locale, $this->getFallbackLanguage()?->code, array_keys($this->getAllAvailableLanguages())))
            ->values()
            ->all();

    }

    public function forgetCachedNavigation(): void
    {
        $this->cacheManager
            ->store(InspireCmsConfig::get('cache.content_routes.store'))
            ->forget(InspireCmsConfig::get('cache.navigation.key'));
    }

    /**
     * @return array
     */
    public function getContentRoutes()
    {
        if (! $this->cachedContentRoutes) {
            $this->cachedContentRoutes = $this->cacheManager
                ->store(InspireCmsConfig::get('cache.content_routes.store'))
                ->remember(
                    InspireCmsConfig::get('cache.content_routes.key'),
                    InspireCmsConfig::get('cache.content_routes.ttl'),
                    fn () => $this->getSerializedContentRoutesForCache()
                );
        }

        return collect($this->cachedContentRoutes['routes'] ?? [])
            ->map(fn ($arr) => array_combine($this->cachedContentRoutes['alias'] ?? [], $arr))
            ->all();
    }

    public function forgetCachedContentRoutes(): void
    {
        $this->cacheManager
            ->store(InspireCmsConfig::get('cache.content_routes.store'))
            ->forget(InspireCmsConfig::get('cache.content_routes.key'));
    }

    // region Helpers
    private function getSerializedLanguagesForCache(): array
    {
        $attributes = ['id', 'code', 'is_default'];

        $alias = $this->aliasModelFields($attributes);

        $languages = $this->getSortedLanguages()
            ->map(fn ($language) => $this->aliasedModel($alias, $language))
            ->all();

        return compact('alias', 'languages');
    }

    private function getSerializedNavigationForCache(): array
    {
        $attributes = ['title', 'url', 'target', 'category', 'type', 'is_active'];
        $relations = ['children'];

        $alias = $this->aliasModelFields($attributes, $relations);

        $models = InspireCmsConfig::getNavigationModelClass()::with(['content', 'children'])
            ->defaultOrder()
            ->get()
            ->toTree();

        $navigation = [];

        foreach ($models as $model) {
            $navigation[] = $this->aliasedNavigation($alias, $model);
        }

        return compact('alias', 'navigation');
    }

    private function getSerializedContentRoutesForCache(): array
    {
        $attributes = ['uri', 'regex_constraints'];

        $alias = $this->aliasModelFields($attributes);

        $records = InspireCmsConfig::getContentRouteModelClass()::query()
            ->whereIsDefaultPattern(false)
            ->distinct('uri')
            ->get($attributes);

        $routes = $records
            ->map(fn ($m) => $this->aliasedModel($alias, $m))
            ->all();

        return compact('alias', 'routes');
    }

    private function getSortedLanguages(): Collection
    {
        return InspireCmsConfig::getLanguageModelClass()::query()
            ->get()
            // Sort languages by default language first
            ->sortBy(fn (Language $lang) => $lang->isDefault() ? -1 : 1)
            ->keyBy('code');
    }

    private function aliasedModel(array $alias, Model $model): array
    {
        return collect($alias)
            ->mapWithKeys(fn ($attribute, $key) => [$key => $model->getAttribute($attribute)])
            ->all();
    }

    private function aliasedNavigation(array $alias, Model $navigation): array
    {
        $allLanguages = $this->getAllAvailableLanguages();

        $result = [];

        foreach ($alias as $key => $attribute) {
            $value = null;
            switch ($attribute) {
                case 'url':
                    $value = collect($allLanguages)
                        ->mapWithKeys(fn ($language) => [
                            $language->code => $navigation->getUrl($language),
                        ])
                        ->all();

                    break;
                case 'children':
                    $value = collect($navigation->{$attribute})
                        ->map(fn ($child) => $this->aliasedNavigation($alias, $child))
                        ->values()
                        ->all();

                    break;
                case class_uses_recursive($navigation, \Spatie\Translatable\HasTranslations::class) &&
                in_array($attribute, $navigation->getTranslatableAttributes()):
                    $value = collect($allLanguages)
                        ->mapWithKeys(fn ($language) => [
                            $language->code => $navigation->getTranslation($attribute, $language->code),
                        ])
                        ->all();

                    break;
                default:
                    $value = $navigation->getAttribute($attribute);

                    break;
            }
            $result[$key] = $value;
        }

        return $result;
    }

    private function aliasModelFields($attributes = [], $relations = []): array
    {
        return array_values(array_unique(array_merge($attributes, $relations)));
    }

    private function processNavigationData(array $navigationData, array $alias)
    {
        return collect($navigationData)
            ->where(fn ($v) => is_array($v))
            ->map(fn ($arr) => array_combine($alias, $arr))
            ->map(function (array $data) use ($alias) {
                if (isset($data['children']) && is_array($data['children'])) {
                    $data['children'] = $this->processNavigationData($data['children'], $alias);
                } else {
                    $data['children'] = [];
                }

                $data['isActive'] = (bool) $data['is_active'];
                unset($data['is_active']);
    
                return $data;
            })
            ->all();
    }

    // endregion Helpers
}
