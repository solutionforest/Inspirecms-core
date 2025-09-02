<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Scopes\ContentVersionDetailScope;
use SolutionForest\InspireCms\Support\Models\Scopes\NestableTreeDetailScope;

class PageActivity extends BaseWidget
{
    protected static int $totalTakeLatest = 5;

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Model $record) => $this->getRecordUrl($record))
            ->poll('60s')
            ->striped()
            ->emptyStateIcon(FilamentIcon::resolve('inspirecms::info'))
            ->emptyStateHeading(__('inspirecms::widgets.page_activity.empty_state.heading'))
            ->modifyQueryUsing(fn ($query) => $query->with([
                'latestNonDraftContentVersion', // To check the content is published or not
                'publishedVersions',
            ]))
            ->columns([
                TextColumn::make('title')
                    ->label(__('inspirecms::resources/content.title.label'))
                    ->grow(),
                TextColumn::make('displayStatus')
                    ->label(__('inspirecms::resources/content.status.label'))
                    ->formatStateUsing(fn (?ContentStatusOption $state) => $state->getLabel())
                    ->color(fn (?ContentStatusOption $state) => $state->getColor())
                    ->icon(fn (?ContentStatusOption $state) => $state->getIcon())
                    ->badge()
                    ->iconPosition(IconPosition::Before)
                    ->width('2%'),

                TextColumn::make('published_at')
                    ->label(__('inspirecms::resources/content.published_at.label'))
                    ->getStateUsing(function (Model $record) {
                        if (($latestNonDraftContentVersion = $record->latestNonDraftContentVersion)) {
                            // If the latest non-draft version exists, check if it is published
                            if ($latestNonDraftContentVersion->publish_state === 'unpublish') {
                                return null;
                            }
                        }

                        return $record->getLatestPublishedTime();
                    })
                    ->placeholder(__('inspirecms::inspirecms.n/a'))
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => ($state && $state instanceof \DateTimeInterface) ? $state->diffForHumans(now()) : null)
                    ->tooltip(fn ($state) => ($state && $state instanceof \DateTimeInterface) ? $state->toDateTimeString() : null)
                    ->width('5%'),
                TextColumn::make('updated_at')
                    ->label(__('inspirecms::resources/content.updated_at.label'))
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans())
                    ->tooltip(fn ($state) => $state?->toDateTimeString())
                    ->width('5%'),
            ]);
    }

    protected function getRecordUrl(Model $record): ?string
    {
        $resource = InspireCmsConfig::getFilamentResource('content', ContentResource::class);

        return FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view', 'index'], ['record' => $record], true);
    }

    protected function getLatestUpdatePagesQuery(): Builder
    {
        return InspireCmsConfig::getContentModelClass()::query()
            ->with(['publishedVersions'])
            ->withoutGlobalScopes([
                NestableTreeDetailScope::class,
            ])
            ->withGlobalScope(ContentVersionDetailScope::class, new ContentVersionDetailScope)
            ->orderByDesc('__latest_version_dt')
            ->orderByDesc('updated_at')
            ->take(static::$totalTakeLatest);
    }

    // region Table Configuration
    protected function makeTable(): Table
    {
        return parent::makeTable()
            ->query($this->getLatestUpdatePagesQuery())
            ->paginated(false);
    }

    protected function getTableHeading(): string | Htmlable | null
    {
        return UIHelper::generateTextWithIcon(
            text: __('inspirecms::widgets.page_activity.title'),
            icon: 'heroicon-o-arrow-trending-up',
            iconColor: 'primary',
        );
    }
    // endregion Table Configuration
}
