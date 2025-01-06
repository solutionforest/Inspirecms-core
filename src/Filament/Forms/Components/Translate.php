<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Pboivin\FilamentPeek\Livewire\BuilderEditor;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;

class Translate extends Component
{
    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.translate';

    protected null | array | Closure $locales = null;

    protected null | string | Closure $defaultLocale = null;

    protected ?string $groupName = null;

    final public function __construct(array $schema = [])
    {
        $this->schema($schema);
    }

    public static function make(array $schema = []): static
    {
        $static = app(static::class, ['schema' => $schema]);
        $static->configure();

        return $static;
    }

    /**
     * @return array<ComponentContainer>
     */
    public function getChildComponentContainers(bool $withHidden = false): array
    {
        $containers = [];

        $locales = $this->getLocales();

        foreach ($locales as $locale) {
            $containers[$locale] = ComponentContainer::make($this->getLivewire())
                ->parentComponent($this)
                ->components(function ($livewire) use ($locale) {

                    $components = [];

                    foreach ($this->getChildComponentsByLocale($locale) as $component) {

                        $component = $this->prepareTranslateLocaleComponent($component, $locale);

                        $component = $this->configureComponentForLivewire($component, $locale, $livewire);

                        $components[] = $component;
                    }

                    return $components;
                })
                ->getClone();
        }

        return $containers;
    }

    public function locales(null | array | Closure $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    public function defaultLocale(null | string | Closure $locale): static
    {
        $this->defaultLocale = $locale;

        return $this;
    }

    public function groupName(?string $groupName): static
    {
        $this->groupName = $groupName;

        $this->statePath($groupName);

        return $this;
    }

    public function getLocales(): array
    {
        return $this->evaluate($this->locales) ??
            collect(InspireCms::getAllAvailableLanguages())
                ->map(fn (LanguageDto $lang) => $lang->code)
                ->values()
                ->all();
    }

    public function getDefaultLocale(): ?string
    {
        return $this->evaluate($this->defaultLocale) ?? InspireCms::getFallbackLanguage()->code;
    }

    public function getGroupName(): ?string
    {
        return $this->evaluate($this->groupName);
    }

    /**
     * @return array<Component>
     */
    public function getChildComponentsByLocale(string $locale): array
    {
        return $this->evaluate($this->childComponents, [
            'locale' => $locale,
        ]);
    }

    // region Helpers
    protected function prepareTranslateLocaleComponent(Component &$component, string $locale): Component
    {
        $localeComponent = clone $component;

        $defaultLocale = $this->getDefaultLocale();

        if ($localeComponent instanceof Field) {

            $localeComponent->label($component->getLabel());

            // Spatie translatable field format
            $componentName = implode('.', [
                $component->getName(),
                $locale,
            ]);

            $localeComponent->name($componentName);

            $localeComponent->statePath($localeComponent->getName());

            // Mark the field as translatable
            $localeComponent->translatable();

            // If the locale is the default locale, we don't need to mark it as required
            if ($localeComponent->isRequired() && $locale !== $defaultLocale) {

                $localeComponent
                    ->markAsRequired()
                    ->required(false);
            }

        } else {

            $childComponents = $localeComponent->getChildComponents();

            if ($childComponents) {
                $localeComponent->schema(
                    collect($childComponents)
                        ->map(fn ($childComponent) => $this->prepareTranslateLocaleComponent($childComponent, $locale))
                        ->all()
                );
            }
        }

        return $localeComponent;
    }

    protected function configureComponentForLivewire(Component &$component, string $locale, $livewire): Component
    {
        switch (true) {
            case $livewire instanceof ContentForm:
                $component = $this->configureComponentForContentForm($component, $locale, $livewire);

                break;
            case $livewire instanceof BuilderEditor:
                $component = $this->configureComponentForBuilderEditor($component, $locale, $livewire);

                break;
        }

        return $component;
    }

    protected function configureComponentForContentForm(Component &$component, string $locale, ContentForm $livewire): Component
    {
        return $component
            ->hidden($locale !== $livewire->getActiveActionsLocale())
            ->dehydratedWhenHidden();
    }

    protected function configureComponentForBuilderEditor(Component &$component, string $locale, BuilderEditor $livewire): Component
    {
        $activeLocale = $livewire->editorData['activeLocale'] ?? null;

        $component
            ->hidden($locale !== $activeLocale)
            ->dehydratedWhenHidden();

        return $component;
    }
    // endregion Helpers
}
