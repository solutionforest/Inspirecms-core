<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Base\Filament\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\ListContentRecords\Concerns\Translatable;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Actions\CreateContentAction;

abstract class BaseContentListPage extends BaseListRecords
{
    use ContentPageTrait;

    // Commented out to insteadof ListRecords\Concerns\Translatable
    // use ListRecords\Concerns\Translatable;
    use Translatable;

    protected static string $view = 'inspirecms::filament.pages.list-content';

    public function getActions(): array
    {
        return [
            CreateContentAction::make(),
        ];
    }

    public function getTabs(): array
    {
        // avoid to display table (performance tuning)
        if (! $this->isDisplayTable()) {
            return [];
        }

        return inspirecms_content_statuses()->all()
            ->mapWithKeys(
                fn (ContentStatusOption $option) => [
                    $option->getName() => Tab::make()
                        ->icon($option->getIcon())
                        ->label($option->getLabel())
                        ->badge($option->getName() != 'unpublish' ? static::getResource()::getEloquentQuery()->where('status', $option->getValue())->whereIsPublished()->count() : null)
                        ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $option->getValue())),
                ]
            )
            ->prepend(Tab::make(), 'all')
            ->toArray();
    }

    public function getParentKey(): string | int | null
    {
        $model = new ($this->getModel())();

        return $model->getRootLevelParentId();
    }

    public function isDisplayTable(): bool
    {
        return false;
    }

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }

    protected function configureAction(Action $action): void
    {
        parent::configureAction($action);

        switch (true) {
            case $action instanceof CreateContentAction:
                $action
                    ->parentContentKey($this->getParentKey());

                break;

        }
    }
}
