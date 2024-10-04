<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class PageActivity extends BaseWidget
{
    protected static int $totalTakeLatest = 5;

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Model $record) => $this->getRecordUrl($record))
            ->poll('60s')
            ->striped()
            ->emptyStateIcon('heroicon-o-information-circle')
            ->emptyStateHeading(__('inspirecms::widgets.page_activity.empty_state.heading'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->grow(),
                Tables\Columns\TextColumn::make('displayStatus')
                    ->label(__('inspirecms::inspirecms.status'))
                    ->formatStateUsing(fn (?ContentStatusOption $state) => $state->getLabel())
                    ->color(fn (?ContentStatusOption $state) => $state->getColor())
                    ->icon(fn (?ContentStatusOption $state) => $state->getIcon())
                    ->badge()
                    ->iconPosition(IconPosition::Before)
                    ->width('2%'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('inspirecms::inspirecms.publish_at'))
                    ->getStateUsing(fn ($record) => $record->getLatestPublishedContentVersion()?->pivot->published_at?->diffForHumans())
                    ->width('5%'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ]);
    }

    protected function getRecordUrl(Model $record): ?string
    {
        $resource = config('inspirecms.resources.page', PageResource::class);

        if (is_subclass_of($resource, Resource::class)) {

            foreach (['view', 'edit', 'index'] as $page) {

                if (! $resource::hasPage($page)) {
                    continue;
                }

                $action = $page === 'index' ? 'access' : $page;

                if (! $resource::{'can' . ucfirst($action)}($record)) {
                    continue;
                }

                try {
                    return $resource::getUrl($page, ['record' => $record]);
                } catch (\Throwable $th) {
                    //
                }
            }
        }

        return null;
    }

    protected function getLatestUpdatePagesQuery(): Builder
    {
        $query = InspireCmsConfig::getContentModelClass()::query();

        return $query->with(['publishedVersions'])->orderByDesc('updated_at')->take(static::$totalTakeLatest);
    }

    //region Table Configuration
    protected function makeTable(): Table
    {
        return parent::makeTable()
            ->query($this->getLatestUpdatePagesQuery())
            ->paginated(false);
    }

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        $icon = 'heroicon-o-document-text';
        $color = 'info';

        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex gap-2">
                <x-filament::icon
                    icon="{{$icon}}"
                    class="h-5 w-5 text-custom-500 dark:text-custom-400"
                    style="{{$iconStyle}}"
                />
                <span>
                    {{ $title }}
                </span>
            </div>
        blade, [
            'title' => __('inspirecms::widgets.page_activity.title'),
            'icon' => $icon,
            'iconStyle' => \Filament\Support\get_color_css_variables(
                $color,
                shades: [400, 500],
                alias: 'infolists::components.icon-entry.item',
            ),
        ]));
    }
    //endregion Table Configuration
}
