<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
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

    protected $listeners = [
        'mountAction',
    ];

    public function getActions(): array
    {
        return [
            CreateContentAction::make()
                ->modelLabel(lcfirst(__('inspirecms::inspirecms.content.singular')))
                ->parentContentKey($this->getParentKey()),
        ];
    }

    public function content(Schema $schema): Schema
    {
        $components[] = $this->getTabsContentComponent();
        if ($this->isDisplayTable()) {
            $components[] = RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE);
            $components[] = EmbeddedTable::make();
            $components[] = RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER);
        } else {
            // Render actions' modal component
            $components[] = View::make('filament-actions::components.modals');
        }

        return $schema
            ->components($components);
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

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Start;
    }
}
