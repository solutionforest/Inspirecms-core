<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Enums\PageStatus;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource;

class ListPages extends ListRecords
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'draft' => Tab::make()
                ->icon(PageStatus::Draft->getIcon())
                ->label(PageStatus::Draft->getLabel())
                ->badge($this->getTableQuery()->where('status', PageStatus::Draft->value)->isPublished(isIncludePrivateUse: false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PageStatus::Draft->value)),
            'private' => Tab::make()
                ->icon(PageStatus::Private->getIcon())
                ->label(PageStatus::Private->getLabel())
                ->badge($this->getTableQuery()->where('status', PageStatus::Private->value)->isPublished(isIncludePrivateUse: true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PageStatus::Private->value)),
            'public' => Tab::make()
                ->icon(PageStatus::Publish->getIcon())
                ->label(PageStatus::Publish->getLabel())
                ->badge($this->getTableQuery()->where('status', PageStatus::Publish->value)->isPublished(isIncludePrivateUse: false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PageStatus::Publish->value)),
            'unpublish' => Tab::make()
                ->icon(PageStatus::Unpublish->getIcon())
                ->label(PageStatus::Unpublish->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', PageStatus::Unpublish->value)),
        ];
    }
}
