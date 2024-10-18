<?php

namespace SolutionForest\InspireCms;

use Filament\Facades\Filament;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Cache\CacheManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Filament\Pages\Auth\Install;
use SolutionForest\InspireCms\Models\Contracts\Language;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class InspireCmsManager
{
    protected CacheManager $cacheManager;

    protected Collection $sections;

    protected ?array $cachedLanguages = null;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;

        $this->sections = collect(config('inspirecms.filament.clusters'))->map(fn ($fqcn, $name) => new ClusterSection($name, $fqcn));
    }

    /**
     * Determine if there is a need to go to the install page
     */
    public function needInstall(): bool
    {
        //region Check user table not empty
        $guard = InspireCmsConfig::getGuardName();

        /** @var ?EloquentUserProvider $provider */
        $provider = auth($guard)?->getProvider();

        if (! $provider) {
            throw new \Exception('Authentication provider not found for guard: ' . $guard);
        }
        if ($provider->getModel()::count() <= 0) {
            return true;
        }
        //endregion Check user table not empty

        return false;
    }

    public function getInstallUrl(): ?string
    {
        return Filament::getPanel(config('insiprecms.filament.panel_id', 'cms'))?->route(Install::getRouteSlug());
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
                config('inspirecms.cache.languages.key'),
                config('inspirecms.cache.languages.ttl'),
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
        $this->cacheManager->forget(config('inspirecms.cache.languages.key'));
    }

    //region Helpers
    private function getSerializedLanguagesForCache(): array
    {
        $attributes = ['code', 'name', 'is_default', 'route_pattern'];

        $alias = $this->aliasModelFields($attributes);

        $languages = $this->getSortedLanguages()
            ->map(fn ($language) => $this->aliasedModel($alias, $language))
            ->all();

        return compact('alias', 'languages');
    }

    private function getSortedLanguages(): Collection
    {
        return InspireCmsConfig::getLanguageModelClass()::query()
            ->get()
            // Sort languages by default language first
            ->sortBy(fn (Language $lang) => $lang->isDefault() ? -1 : 1)
            ->keyBy('code');
    }

    private function aliasedModel(array $alias, Model $language): array
    {
        return collect($alias)
            ->mapWithKeys(fn ($attribute, $key) => [$key => $language->getAttribute($attribute)])
            ->all();
    }

    private function aliasModelFields($attributes = []): array
    {
        return array_values(array_unique($attributes));
    }
    //endregion Helpers
}
