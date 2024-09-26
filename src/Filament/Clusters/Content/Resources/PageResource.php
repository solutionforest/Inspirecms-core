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
            'view' => Pages\ViewPage::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('documentType', fn ($q) => $q->where('is_web_page', true));
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

    /**
     * @return array<NavigationItem>
     */
    public static function getCustomNavigationItems(array $parameters = []): array
    {
        $navigationLabel = static::getNavigationLabel();
        if (isset($parameters['parent'])) {
            if ($parameters['parent'] instanceof Model && $parameters['parent']->exists) {
                $navigationLabel = static::getRecordTitle($parameters['parent']);
            } elseif (! empty($parameters['parentTitle'])) {
                $navigationLabel = $parameters['parentTitle'];
                unset($parameters['parentTitle']);
            } elseif (is_int($parameters['parent']) || is_string($parameters['parent'])) {
                $record = static::getModel()::find($parameters['parent']);
                if ($record) {
                    $navigationLabel = static::getRecordTitle($record);
                }
            }
        }
        $isActive = function () use ($parameters) {
            if (! request()->routeIs(static::getRouteBaseName() . '.*')) {
                return false;
            }
            if (isset($parameters['parent'])) {
                $parentKey = $parameters['parent'] instanceof Model ? $parameters['parent']->getKey() : $parameters['parent'];

                return request()->query('parent') == $parentKey;
            }

            return false;
        };

        return [
            NavigationItem::make($navigationLabel)
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen($isActive)
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->badgeTooltip(static::getNavigationBadgeTooltip())
                ->sort(static::getNavigationSort())
                ->url(static::getCustomNavigationUrl($parameters)),
        ];
    }

    public static function getCustomNavigationUrl(array $parameters = []): string
    {
        return static::getUrl('index', $parameters);
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.page');
    }
}
