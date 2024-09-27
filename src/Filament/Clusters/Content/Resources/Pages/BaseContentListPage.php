<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;

abstract class BaseContentListPage extends BaseListPage
{
    use ContentPageTrait;

    public function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return inspirecms_content_statuses()->all()
            ->mapWithKeys(
                fn (ContentStatusOption $option) => [
                    $option->getName() => Tab::make()
                        ->icon($option->getIcon())
                        ->label($option->getLabel())
                        ->badge($option->getName() != 'unpublish' ? static::getResource()::getEloquentQuery()->where('status', $option->getValue())->isPublished()->count() : null)
                        ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $option->getValue())),
                ]
            )
            ->prepend(Tab::make(), 'all')
            ->toArray();
    }
}
