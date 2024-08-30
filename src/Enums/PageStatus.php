<?php

namespace SolutionForest\InspireCms\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PageStatus: int implements HasColor, HasIcon, HasLabel
{
    case Draft = 0;
    case Publish = 1;
    case Private = 3;
    case Unpublish = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __('inspirecms::inspirecms.page_status.draft.label'),
            self::Publish => __('inspirecms::inspirecms.page_status.publish.label'),
            self::Private => __('inspirecms::inspirecms.page_status.private.label'),
            self::Unpublish => __('inspirecms::inspirecms.page_status.unpublish.label'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Publish => 'success',
            self::Private => 'secondary',
            self::Unpublish => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil',
            self::Publish => 'heroicon-o-check-circle',
            self::Private => 'heroicon-o-lock-closed',
            self::Unpublish => 'heroicon-o-x-circle',
            default => null,
        };
    }
}
