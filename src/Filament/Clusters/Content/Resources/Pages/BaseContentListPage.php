<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Actions\CreateContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

abstract class BaseContentListPage extends BaseListPage implements HasModelExplorer
{
    use ContentPageTrait;
    use ListRecords\Concerns\Translatable;

    protected static string $view = 'inspirecms::filament.pages.content.list';

    public function getActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            CreateContentAction::make()
                ->modifyUrlParameterUsing(function (array $parameters) {
                    return array_merge($parameters, ['parent' => $this->getParentKey()]);
                }),
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

    public function getParentKey(): string | int | null
    {
        $model = new ($this->getModel())();

        return $model->getNestableRootValue();
    }

    public function isDisplayTable(): bool
    {
        return false;
    }

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }
}
