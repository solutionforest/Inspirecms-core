<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Concerns;

trait InteractsWithContentTreeModal
{
    public function getContentTreeModalConfig(): array
    {
        return [];
    }

    public function getContentTreeModalId(): string
    {
        return 'content-tree-picker-modal';
    }
}
