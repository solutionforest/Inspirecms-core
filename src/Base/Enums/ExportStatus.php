<?php

namespace SolutionForest\InspireCms\Base\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExportStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Failed = 'failed';
    case Finished = 'finished';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => __('inspirecms::messages.pending'),
            self::InProgress => __('inspirecms::messages.in_progress'),
            self::Failed => __('inspirecms::messages.failed'),
            self::Finished => __('inspirecms::messages.finished'),
            default => $this->name,
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::InProgress => 'info',
            self::Failed => 'danger',
            self::Finished => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending, self::InProgress => 'heroicon-o-clock',
            self::Failed => 'heroicon-o-x-circle',
            self::Finished => 'heroicon-o-check-circle',
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::Finished;
    }

    public function isPaused(): bool
    {
        return $this === self::InProgress;
    }

    public function isFailed(): bool
    {
        return $this === self::Failed;
    }
}
