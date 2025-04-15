<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use Illuminate\Support\Arr;

class MarkdownConverter extends BaseConverter
{
    /**
     * @var array<string, mixed>
     *
     * @link https://commonmark.thephpleague.com/2.4/configuration/
     */
    protected array $mdConfigs = [];

    /**
     * @var array<string, mixed>
     *
     * @link https://commonmark.thephpleague.com/2.4/extensions/overview/
     */
    protected array $mdExtensions = [];

    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $value = $this->applyLocaleConversion($sourceValue, $locale, $fallbackLocale);

        if (! $value) {
            return $value;
        }

        if (! $this->isFieldTypeTranslatable() && is_array($value)) {
            $value = Arr::first($value);
        }

        if (is_array($value)) {
            return Arr::map($value, function ($item) {
                return $this->convertMarkdown($item);
            });
        }

        return $this->convertMarkdown($value);
    }

    public function setConfigs(array $configs, bool $merge = true): static
    {
        if ($merge) {
            $this->mdConfigs = array_merge($this->mdConfigs, $configs);
        } else {
            $this->mdConfigs = $configs;
        }

        return $this;
    }

    public function setExtensions(array $extensions, bool $merge = true): static
    {
        if ($merge) {
            $this->mdExtensions = array_merge($this->mdExtensions, $extensions);
        } else {
            $this->mdExtensions = $extensions;
        }

        return $this;
    }

    private function convertMarkdown($value)
    {
        try {
            if (! is_string($value)) {
                return $value;
            }

            $value = str($value)->markdown($this->mdConfigs, $this->mdExtensions)->toHtmlString();

            return $value;

        } catch (\Throwable $th) {
            error_log('Markdown conversion failed: ' . $th->getMessage());

            return $value;
        }
    }
}
