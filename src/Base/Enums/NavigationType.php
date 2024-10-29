<?php

namespace SolutionForest\InspireCms\Base\Enums;

enum NavigationType: string implements Interfaces\NavigationType
{
    case Content = 'content';

    case Link = 'link';

    case Group = 'group';

    public function getLabel(): ?string
    {
        return match ($this) {
            default => $this->name,
        };
    }

    public static function getDefaultValue(): Interfaces\NavigationType
    {
        return self::Group;
    }

    public function canEditIsVisible(): bool
    {
        return $this->value !== self::Content->value;
    }
}
