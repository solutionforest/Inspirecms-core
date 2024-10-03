<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources;

use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCms\Filament\Clusters\Content;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;

class PageResource extends BaseContentResource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -9;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Content::class;

    public static function getPermissionPrefixes(): array
    {
        return parent::getBasePermissionPrefixes();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
            'view' => Pages\ViewPage::route('/{record}/view'),
            'trash' => Pages\Trashes::route('/trashes'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('documentType', fn ($q) => $q->where('is_web_page', true));
    }
    
    public static function getNavigationItems(): array
    {
        $hasTrashPage = static::hasPage('trash');
        if (!$hasTrashPage) {
            return parent::getNavigationItems();
        }
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn () => request()->routeIs(static::getRouteBaseName() . '.*') && !request()->routeIs(static::getRouteBaseName() . '.trash'))
                ->sort(static::getNavigationSort())
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->badgeTooltip(static::getNavigationBadgeTooltip())
                ->url(static::getNavigationUrl()),
            NavigationItem::make(fn () => __('inspirecms::inspirecms.trash'))
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon('heroicon-o-trash')
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.trash'))
                ->sort(9999)
                ->url(static::getUrl('trash')),
        ];
    }

    //region Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex gap-x-2 items-center">
                <span class="flex-1 font-semibold">
                    {{ $title }}
                </span>
                <x-filament::badge class="font-mono">
                    {{ $badge }}
                </x-filament::badge>
            </div>
        blade, ['title' => static::getRecordTitle($record), 'badge' => $record->slug]));
    }
    //endregion Global search

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.page');
    }
}
