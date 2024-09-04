<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;

interface ContentStatusManifestInterface
{
    /**
     * Adds a new option to the manifest.
     *
     * @param  ContentStatusOption  $option  The option to add.
     * @param  bool  $replace  Whether to replace an existing option with the same value.
     */
    public function addOption(ContentStatusOption $option, bool $replace = false): void;

    /**
     * Replaces an existing option in the manifest.
     *
     * @param  ContentStatusOption  $option  The option to replace.
     */
    public function replaceOption(ContentStatusOption $option): void;

    /**
     * Retrieves an option by its value.
     *
     * @param  int|string  $valueOrName  The value/name of the option to retrieve.
     * @return ContentStatusOption|null The option if found, null otherwise.
     */
    public function getOption(int | string $valueOrName): ?ContentStatusOption;

    /**
     * Retrieves all available key/value select options from the manifest.
     *
     * @return Collection<string, ContentStatusOption> A collection of key-value pairs where the key is the option value and the value is the ContentStatusOption instance.
     */
    public function selectOptions(): Collection;

    /**
     * Retrieves all available options from the manifest.
     *
     * @return Collection A collection of ContentStatusOption instances.
     */
    public function all(): Collection;

    public function setDefaultValue(int $value): void;

    public function getDefaultValue(): ?int;
}
