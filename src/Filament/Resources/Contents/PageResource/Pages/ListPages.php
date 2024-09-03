<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\DataTypes\ContentStatusOption;
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
        return inspirecms_content_statuses()->all()
            ->mapWithKeys(
                fn (ContentStatusOption $option) => [
                    $option->name => Tab::make()
                        ->icon($option->getIcon())
                        ->label($option->getLabel())
                        ->badge($option->name != 'unpublish' ? static::getResource()::getEloquentQuery()->where('status', $option->value)->isPublished()->count() : null)
                        ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $option->value)),
                ]
            )
            ->prepend(Tab::make(), 'all')
            ->toArray();
    }
}
