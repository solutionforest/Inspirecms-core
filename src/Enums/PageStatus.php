<?php

namespace SolutionForest\InspireCms\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PageStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Publish = 'publish';
    case SchedulePublish = 'schedule_publish';
    case Private = 'private';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => __('inspirecms::inspirecms.page_status.pending.label'),
            self::Publish => __('inspirecms::inspirecms.page_status.publish.label'),
            self::SchedulePublish => __('inspirecms::inspirecms.page_status.schedule_publish.label'),
            self::Private => __('inspirecms::inspirecms.page_status.private.label'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Publish => 'success',
            self::SchedulePublish => 'info',
            self::Private => 'gray',
        };
    }
}
