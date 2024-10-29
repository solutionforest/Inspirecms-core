<?php

namespace SolutionForest\InspireCms\Base\Enums;

use Filament\Support\Contracts\HasLabel;

enum SitemapChangeFrequency: string implements HasLabel
{
    case Always = 'always';
    case Hourly = 'hourly';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case Never = 'never';

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
