<?php

namespace SolutionForest\InspireCms\Base\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ImportStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Failed = 'failed';
    case Finished = 'finished';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => __('inspirecms::messages.pending'),
            self::Failed => __('inspirecms::messages.failed'),
            self::Finished => __('inspirecms::messages.finished'),
            default => $this->name,
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Failed => 'danger',
            self::Finished => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Failed => 'heroicon-o-x-circle',
            self::Finished => 'heroicon-o-check-circle',
        };
    }
}
