<?php

namespace SolutionForest\InspireCms\Base\Enums;

use Filament\Support\Contracts\HasLabel;

enum SitemapChangeFrequency: int implements HasLabel
{
    case Always = 1;
    case Hourly = 2;
    case Daily = 3;
    case Weekly = 4;
    case Monthly = 5;
    case Yearly = 6;
    case Never = 7;

    public function getLabel(): ?string
    {
        switch ($this->value) {
            case self::Always->value:
                return __('inspirecms::inspirecms.frequency.always.label');
            case self::Hourly->value:
                return __('inspirecms::inspirecms.frequency.hourly.label');
            case self::Daily->value:
                return __('inspirecms::inspirecms.frequency.daily.label');
            case self::Weekly->value:
                return __('inspirecms::inspirecms.frequency.weekly.label');
            case self::Monthly->value:
                return __('inspirecms::inspirecms.frequency.monthly.label');
            case self::Yearly->value:
                return __('inspirecms::inspirecms.frequency.yearly.label');
            case self::Never->value:
                return __('inspirecms::inspirecms.frequency.never.label');
            default:
                return null;
        }
    }
}
