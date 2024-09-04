<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;

class ContentStatusManifest implements ContentStatusManifestInterface
{
    /** @var Collection<ContentStatusOption> */
    protected Collection $options;

    protected int $defaultValue = 0;

    public function __construct()
    {
        $this->options = collect(static::getDefaultOptions());
    }

    /** {@inheritDoc} */
    public function addOption(ContentStatusOption $option, bool $replace = false): void
    {
        $existing = $this->options
            ->where(fn (ContentStatusOption $optionToCheck) => $optionToCheck->getValue(), $option->getValue())
            ->where(fn (ContentStatusOption $optionToCheck) => $optionToCheck->getName(), $option->getName())
            ->first();
        if (! $existing || ($existing && $replace)) {
            $this->options->push($option);
        }
    }

    /** {@inheritDoc} */
    public function replaceOption(ContentStatusOption $option): void
    {
        $this->addOption($option, true);
    }

    /** {@inheritDoc} */
    public function getOption(int | string $valueOrName): ?ContentStatusOption
    {
        if (is_int($valueOrName)) {
            return $this->options->firstWhere(fn (ContentStatusOption $option) => $option->getValue(), $valueOrName);
        } else {
            return $this->options->firstWhere(fn (ContentStatusOption $option) => $option->getName(), $valueOrName);
        }
    }

    /** {@inheritDoc} */
    public function selectOptions(): Collection
    {
        return $this->options->keyBy('value');
    }

    /** {@inheritDoc} */
    public function all(): Collection
    {
        return $this->options;
    }

    public function setDefaultValue(int $value): void
    {
        $this->defaultValue = $value;
    }

    public function getDefaultValue(): ?int
    {
        return $this->defaultValue;
    }

    protected static function getDefaultOptions(): array
    {
        return [
            new ContentStatusOption(
                0,
                'draft',
                __('inspirecms::inspirecms.page_status.draft.label'),
                'warning',
                'heroicon-o-pencil',
            ),
            new ContentStatusOption(
                1,
                'publish',
                __('inspirecms::inspirecms.page_status.publish.label'),
                'success',
                'heroicon-o-check-circle'
            ),
            new ContentStatusOption(
                2,
                'unpublish',
                __('inspirecms::inspirecms.page_status.unpublish.label'),
                'gray',
                'heroicon-o-x-circle'
            ),
            new ContentStatusOption(
                3,
                'private',
                __('inspirecms::inspirecms.page_status.private.label'),
                'secondary',
                'heroicon-o-lock-closed'
            ),
        ];
    }
}
