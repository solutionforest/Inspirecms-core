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

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Main => 'info',
            self::Footer => 'success',
        };
    }

    public static function getDefaultValue(): Interfaces\NavigationCategory
    {
        return self::Main;
    }
}
