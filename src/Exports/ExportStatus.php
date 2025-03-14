<?php

namespace SolutionForest\InspireCms\Exports;

enum ExportStatus
{
    case Completed;
    case Paused;
    case Failed;

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    public function isPaused(): bool
    {
        return $this === self::Paused;
    }

    public function isFailed(): bool
    {
        return $this === self::Failed;
    }
}
