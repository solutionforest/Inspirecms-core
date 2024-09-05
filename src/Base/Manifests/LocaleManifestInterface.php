<?php

namespace SolutionForest\InspireCms\Base\Manifests;

interface LocaleManifestInterface
{
    public function addLocale(string $locale): void;

    public function selectOptions(?string $displayLocale = null): array;
}
