<?php

namespace SolutionForest\InspireCms\Filament\Navigation;

class NavigationItem extends \Filament\Navigation\NavigationItem
{
    protected ?string $section = null;

    protected ?string $itemKey = null;

    public function section(string $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function itemKey(string $itemKey): static
    {
        $this->itemKey = $itemKey;

        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function getItemKey(): ?string
    {
        return $this->itemKey;
    }
}
