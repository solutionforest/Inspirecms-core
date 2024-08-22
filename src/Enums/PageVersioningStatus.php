<?php

namespace SolutionForest\InspireCms\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PageVersioningStatus: string implements HasLabel, HasColor
{
    case Draft = 'draft';
    case Published = 'published';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => __('inspirecms::inspirecms.page_versioning_status.draft.label'),
            self::Published => __('inspirecms::inspirecms.page_versioning_status.published.label'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
        };
    }
}
