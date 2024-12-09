<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

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
                    ->label(__('inspirecms::resources/content.title.label'))
                    ->grow(),
                Tables\Columns\TextColumn::make('displayStatus')
                    ->label(__('inspirecms::resources/content.status.label'))
                    ->formatStateUsing(fn (?ContentStatusOption $state) => $state->getLabel())
                    ->color(fn (?ContentStatusOption $state) => $state->getColor())
                    ->icon(fn (?ContentStatusOption $state) => $state->getIcon())
                    ->badge()
                    ->iconPosition(IconPosition::Before)
                    ->width('2%'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('inspirecms::resources/content.published_at.label'))
                    ->getStateUsing(fn ($record) => $record->getLatestPublishedContentVersion()?->pivot->published_at?->diffForHumans())
                    ->width('5%'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('inspirecms::resources/content.updated_at.label'))
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ]);
    }

    protected function getRecordUrl(Model $record): ?string
    {
        $resource = InspireCmsConfig::get('filament.resources.page', PageResource::class);

        return FilamentResourceHelper::attemptToGetUrl($resource, ['edit', 'view', 'index'], ['record' => $record], true);
    }

    protected function getLatestUpdatePagesQuery(): Builder
    {
        $query = InspireCmsConfig::getContentModelClass()::with([
            'publishedVersions',
        ])->withoutGlobalScopes([
            \SolutionForest\InspireCms\Support\Models\Scopes\NestableTreeDetailScope::class,
        ]);

        return $query->orderByDesc('updated_at')->take(static::$totalTakeLatest);
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
        return UIHelper::generateTextWithIcon(
            __('inspirecms::widgets.page_activity.title'),
            'heroicon-o-document-text',
            'info',
        );
    }
    //endregion Table Configuration
}
