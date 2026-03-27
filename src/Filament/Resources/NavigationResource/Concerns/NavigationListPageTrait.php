<?php

namespace SolutionForest\InspireCms\Filament\Resources\NavigationResource\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;

trait NavigationListPageTrait
{
    public function bootNavigationListPageTrait()
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_HEADER_WIDGETS_BEFORE,
            function () {

                $resource = static::getResource();

                $pageFqcn = collect($resource::getPages())
                    ->only(['index', 'table'])
                    ->whereInstanceOf(PageRegistration::class)
                    ->map(fn (PageRegistration $v) => $v->getPage())
                    ->all();

                $pageActions = collect($pageFqcn)
                    ->where(fn ($fqcn) => is_a($fqcn, Page::class, true))
                    ->map(function (string $fqcn, $type) use ($resource) {

                        /** @var class-string<Page> $fqcn */

                        return Action::make("{$type}-page")
                            ->label($fqcn::getNavigationLabel())
                            // ->url($fqcn::getUrl())
                            ->url($resource::getUrl($type))
                            ->color('gray')
                            ->icon(match ($type) {
                                'index' => Heroicon::OutlinedQueueList,
                                'table' => Heroicon::OutlinedTableCells,
                                default => null,
                            })
                            ->outlined($this instanceof $fqcn ? false : true);
                    })
                    ->values()
                    ->map(function (Action $action, $index) {
                        return $action
                            ->iconPosition($index === 0 ? IconPosition::Before : IconPosition::After);
                    })
                    ->all();

                $btnGrp = ActionGroup::make($pageActions)
                    ->buttonGroup()
                    ->size('xl');

                return $btnGrp->toHtmlString();
            },
            [static::class],
        );
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? str(class_basename(static::class))
            ->kebab()
            ->afterLast('-')
            ->title();
    }

    public function getBreadcrumb(): ?string
    {
        return static::$breadcrumb ?? str(class_basename(static::class))
            ->kebab()
            ->afterLast('-')
            ->title();
    }
}
