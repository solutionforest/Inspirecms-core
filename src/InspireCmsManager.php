<?php

namespace SolutionForest\InspireCms;

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
use SolutionForest\InspireCms\Filament\Pages\Auth\Install;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Http\Controllers\ContentController;
use SolutionForest\InspireCms\Models\Contracts\Language;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class InspireCmsManager
{
    protected CacheManager $cacheManager;

    protected Collection $sections;

    protected ?array $cachedLanguages = null;

    protected ?array $cachedNavigation = null;

    protected ?array $cachedContentRoutes = null;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;

        $this->sections = collect(InspireCmsConfig::get('filament.clusters'))->map(fn ($fqcn, $name) => new ClusterSection($name, $fqcn));
    }

    /**
     * Determine if there is a need to go to the install page
     */
    public function needInstall(): bool
    {
        // region Check user table not empty
        $guard = InspireCmsConfig::getGuardName();

        /** @var ?EloquentUserProvider $provider */
        $provider = auth($guard)?->getProvider();

        if (! $provider) {
            throw new \Exception('Authentication provider not found for guard: ' . $guard);
        }
        if ($provider->getModel()::count() <= 0) {
            return true;
        }
        // endregion Check user table not empty

        return false;
    }

    public function getInstallUrl(): ?string
    {
        return Filament::getPanel(InspireCmsConfig::get('filament.panel_id', 'cms'))?->route(Install::getRouteSlug());
    }

    public function getImportDataUrl(): ?string
    {
        try {

            $resource = InspireCmsConfig::get('resources.import', \SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportResource::class);

            return FilamentResourceHelper::attemptToGetUrl($resource, 'index', [], true);

        } catch (RouteNotFoundException $th) {
            return null;
        }
    }

    /**
     * @return \Illuminate\Support\Collection<\SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection>
     */
    public function getSections(...$names): Collection
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
        Route::name('inspirecms.asset')
            ->get('assets/{key}', \SolutionForest\InspireCms\Http\Controllers\AssetController::class)
            ->middleware(InspireCmsConfig::get('media_library.middlewares'));

        Route::name('inspirecms.sitemap')
            ->get('sitemap.xml', \SolutionForest\InspireCms\Http\Controllers\SitemapController::class);

        Route::name('inspirecms.content.')
            ->middleware(InspireCmsConfig::get('content.routes.middlewares', []))
            ->group(function () {

                $factory = ContentSegmentFactory::create();

                if (Schema::hasTable(InspireCmsConfig::getContentRouteTableName()) && Schema::hasTable('cache')) {

                    foreach ($this->getContentRoutes() as $index => $item) {
                        Route::get($item['uri'], ContentController::class)
                            ->where($item['regex_constraints'] ?? [])
                            ->name($item['alias'] ?? 'content_' . $index);
                    }
                }

                // default route
                Route::get($factory->getDefaultRoutePattern(), ContentController::class)
                    ->where($factory->getDefaultRouteConstraints())
                    ->name('default');
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
            $this->cachedLanguages = $this->cacheManager->remember(
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
        $this->cacheManager->forget(InspireCmsConfig::get('cache.languages.key'));
    }

    /**
     * @return NavigationDto[]
     */
    public function getNavigation(string $category, ?string $locale = null): array
    {
        if (! $this->cachedNavigation) {
            $this->cachedNavigation = $this->cacheManager->remember(
                InspireCmsConfig::get('cache.navigation.key'),
                InspireCmsConfig::get('cache.navigation.ttl'),
                fn () => $this->getSerializedNavigationForCache()
            );
        }

        return collect($this->cachedNavigation['navigation'] ?? [])
            ->map(function ($arr) {
                $alias = $this->cachedNavigation['alias'] ?? [];
                $data = array_combine($alias, $arr);
                if (isset($data['children'])) {
                    $data['children'] = collect($data['children'])
                        ->map(fn ($childArr) => array_combine($alias, $childArr))
                        ->values()
                        ->all();
                }

                $data['isActive'] = (bool) $data['is_active'];

                return $data;
            })
            ->map(fn ($arr) => NavigationDto::fromTranslatableArray($arr, $locale, $this->getFallbackLanguage()?->code, array_keys($this->getAllAvailableLanguages())))
            ->where(
                fn (NavigationDto $nav) => $nav->category == $category &&
                $nav->isActive
            )
            ->values()
            ->all();
    }

    public function forgetCachedNavigation(): void
    {
        $this->cacheManager->forget(InspireCmsConfig::get('cache.navigation.key'));
    }

    /**
     * @return array
     */
    public function getContentRoutes()
    {
        if (! $this->cachedContentRoutes) {
            $this->cachedContentRoutes = $this->cacheManager->remember(
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
        $this->cacheManager->forget(InspireCmsConfig::get('cache.content_routes.key'));
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

    // endregion Helpers
}
