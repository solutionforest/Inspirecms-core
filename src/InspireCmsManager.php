<?php

namespace SolutionForest\InspireCms;

use Filament\Facades\Filament;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\ClusterSection;
use SolutionForest\InspireCms\Filament\Clusters;
use SolutionForest\InspireCms\Filament\Pages\Auth\Install;
use SolutionForest\InspireCms\Models\Contracts\Language;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class InspireCmsManager
{
    protected Collection $sections;

    public function __construct()
    {
        $this->sections = collect([
            new ClusterSection('content', Clusters\Content::class),
            new ClusterSection('setting', Clusters\Settings::class),
            new ClusterSection('user', Clusters\Users::class),
        ]);
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
        return Filament::getPanel('cms')?->route(Install::getRouteSlug());
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
     * @return \Illuminate\Support\Collection<\SolutionForest\InspireCms\Models\Contracts\Language
     */
    public function getAllAvailableLanguages(): Collection
    {
        // TODO: cahcing
        $languages = InspireCmsConfig::getLanguageModelClass()::all()->keyBy('code');

        /** @var ?Language */
        $defaultLanguage = $languages->firstWhere(fn (Language $lang) => $lang->isDefault());

        if ($defaultLanguage) {
            $languages = collect($languages)
                ->reduce(function ($array, Language $lang) use ($defaultLanguage) {
                    $collect = collect($array);

                    if ($lang->getCode() === $defaultLanguage->getCode()) {
                        $collect->put(-1, $lang);
                    }

                    $collect->push($lang);

                    return $collect;
                })
                ->sortKeys()
                ->keyBy(fn (Language $lang) => $lang->getCode());
        }

        return $languages;
    }

    public function getFallbackLanguage(): ?Language
    {
        return $this->getAllAvailableLanguages()->first(fn (Language $lang) => $lang->isDefault());
    }
}
