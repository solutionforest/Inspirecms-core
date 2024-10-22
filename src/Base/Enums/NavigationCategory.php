<?php

namespace SolutionForest\InspireCms\Base\Enums;

enum NavigationCategory: string implements Interfaces\NavigationCategory
{
    case Main = 'main';

    case Footer = 'footer';

    public function getLabel(): ?string
    {
        return match ($this) {
            default => $this->name,
        };
    }

    public static function getDefaultValue(): Interfaces\NavigationCategory
    {
        return self::Main;
    }
}
