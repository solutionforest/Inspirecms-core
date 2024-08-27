<?php

namespace SolutionForest\InspireCms\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PageStatus: int implements HasColor, HasLabel
{
    case Draft = 0;
    case Publish = 1;
    case Private = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __('inspirecms::inspirecms.page_status.draft.label'),
            self::Publish => __('inspirecms::inspirecms.page_status.publish.label'),
            self::Private => __('inspirecms::inspirecms.page_status.private.label'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Publish => 'success',
            self::Private => 'gray',
        };
    }
}
