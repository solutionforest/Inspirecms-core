<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Tables;

use DateTimeInterface;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;

use function Filament\Support\get_color_css_variables;

class ContentVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->with(['publishLog', 'author', 'content'])
            )
            ->defaultSort('created_at', 'desc')
            ->heading('')
            ->searchPlaceholder(__('inspirecms::resources/content-version.tables.search_placeholder'))
            ->emptyStateHeading(__('inspirecms::resources/content-version.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/content-version.empty_state.description'))
            ->columns([
                Split::make([
                    Stack::make([

                        TextColumn::make('id')
                            ->label(__('inspirecms::inspirecms.id'))
                            ->prefix('#')
                            ->weight('semibold')
                            ->size('sm')
                            ->fontFamily('mono'),

                        TextColumn::make('created_at')
                            ->label(__('inspirecms::inspirecms.created_at'))
                            ->dateTime('Y-m-d H:i:s')
                            ->sortable(),

                        TextColumn::make('author_name')
                            ->searchable(true, fn ($query, $search) => $query->whereHas('author', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            }))
                            ->color('gray')
                            ->size('sm')
                            ->prefix(fn (ContentVersion | Model $record) => ucfirst($record->event_name) . ' by ')
                            ->getStateUsing(fn (ContentVersion | Model $record) => $record->author?->name ?? __('inspirecms::inspirecms.unknown_user')),
                    ]),
                    TextColumn::make('publish_state')
                        ->badge()
                        ->getStateUsing(fn ($record) => ! blank($record->publish_state) ? inspirecms_content_statuses()->getOption($record->publish_state) : null)
                        ->formatStateUsing(fn ($state) => $state instanceof ContentStatusOption ? $state->getLabel() : null)
                        ->color(fn ($state) => $state instanceof ContentStatusOption ? $state->getColor() : 'gray')
                        ->placeholder(__('inspirecms::inspirecms.n/a'))
                        ->icon(function ($state, ContentVersion | Model $record) {

                            if (($unpublishOption = ContentStatusManifest::getOption('unpublish'))) {
                                if ($state instanceof ContentStatusOption) {
                                    $state = $state->getValue();
                                }
                                if ($state === $unpublishOption->getValue()) {
                                    return 'heroicon-o-eye-slash';
                                }
                            }

                            if ($record->publishLog?->published_at != null) {
                                return 'heroicon-o-eye';
                            }

                            return null;
                        })
                        ->alignEnd(),
                ]),

                TextColumn::make('publishLog.published_at')
                    ->dateTime()
                    ->formatStateUsing(fn ($state) => __('inspirecms::inspirecms.publish_at_xxx', [
                        'time' => $state?->format('Y-m-d H:i:s'),
                    ]))
                    ->suffix(function ($state) {
                        if (empty($state) || ! $state instanceof DateTimeInterface) {
                            return null;
                        }

                        return ' (' . $state->diffForHumans() . ')';
                    })
                    ->color('primary')
                    ->icon('heroicon-o-clock')
                    ->alignCenter()
                    ->tooltip(fn ($state) => $state?->format('Y-m-d H:i:s') ?? __('inspirecms::messages.no_published_yet'))
                    ->extraAttributes(fn ($state) => [
                        'class' => filled($state) ? 'publish-time-badge' : 'hidden',
                        'style' => get_color_css_variables('primary', shades: [50, 400, 600]),
                    ]),

            ])
            ->filters([

                SelectFilter::make('publish_state')
                    ->label(__('inspirecms::resources/content-version.publish_state.label'))
                    ->options(function () {
                        return ContentStatusManifest::all()
                            ->mapWithKeys(fn (ContentStatusOption $option) => [$option->getName() => $option->getLabel()])
                            ->all();
                    })
                    ->default(null)
                    ->multiple()
                    ->placeholder('All States'),

                TernaryFilter::make('avoid_to_clean')
                    ->label(__('inspirecms::resources/content-version.avoid_to_clean.label')),

            ]);
    }
}
