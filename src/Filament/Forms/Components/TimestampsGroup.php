<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;

class TimestampsGroup extends Group
{
    protected function setUp(): void
    {
        parent::setUp();

        if (blank($this->childComponents)) {

            $this
                ->visibleOn('edit')
                ->schema([
                    
                    Placeholder::make('created_at')
                        ->content(fn ($record) => $record?->created_at)
                        ->label(__('inspirecms::inspirecms.created_at'))
                        ->inlineLabel(),
                    Placeholder::make('updated_at')
                        ->content(fn ($record) => $record?->updated_at)
                        ->label(__('inspirecms::inspirecms.last_updated_at'))
                        ->inlineLabel()
                ]);
        }
    }
}
