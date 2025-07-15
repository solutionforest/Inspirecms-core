<?php

namespace SolutionForest\InspireCms\Livewire;

use DateTimeInterface;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\Models\Contracts\ContentVersion;
use SolutionForest\InspireCms\Support\Diff\Diff;

class ContentVersionHistory extends RelationManager implements HasActions, HasForms, HasTable
{
    private const PAGE_NAME = 'cv_page';

    protected static string $relationship = 'contentVersions';

    protected static string $view = 'inspirecms::livewire.content-version-history';

    protected $queryString = [
        'page' => ['except' => 1, 'as' => self::PAGE_NAME],
    ];

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with(['publishLog', 'author'])
            )
            ->defaultSort('created_at', 'desc')
            ->heading('')
            ->searchPlaceholder(__('inspirecms::resources/content-version.tables.search_placeholder'))
            ->emptyStateHeading(__('inspirecms::resources/content-version.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/content-version.empty_state.description'))
            ->columns([
                Stack::make([
                    Split::make([
                        Stack::make([
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
                                ->prefix('By ')
                                ->getStateUsing(fn ($record) => $record->author?->name ?? __('inspirecms::inspirecms.unknown_user')),
                        ]),
                        TextColumn::make('publish_state')
                            ->badge()
                            ->getStateUsing(fn ($record) => !blank($record->publish_state) ? inspirecms_content_statuses()->getOption($record->publish_state) : null)
                            ->formatStateUsing(fn ($state) => $state instanceof ContentStatusOption ? $state->getLabel() : null)
                            ->color(fn ($state) => $state instanceof ContentStatusOption ? $state->getColor() : 'gray')
                            ->placeholder(__('inspirecms::inspirecms.n/a'))
                            ->icon(function ($state, ContentVersion|Model $record) {
        
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
                    ]),
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
                        'style' => \Filament\Support\get_color_css_variables('primary', shades: [50, 400, 600]),
                    ]),

            ])
            ->actions([
                TableAction::make('toggleAvoidToClean')
                    ->label(fn (Model | ContentVersion $record) => $this->getAvoidToCleanActionConfigFromRecord($record)['label'] ?? null)
                    ->color(fn (Model | ContentVersion $record): mixed => $this->getAvoidToCleanActionConfigFromRecord($record)['color'] ?? null)
                    ->icon(fn (Model | ContentVersion $record) => $this->getAvoidToCleanActionConfigFromRecord($record)['icon'] ?? null)
                    ->action(function (Model | ContentVersion $record, TableAction $action) {

                        $record->avoid_to_clean = ! $record->avoid_to_clean;
                        $record->save();
                        $record->refresh(); 

                        $notificationConfig = $this->getAvoidToCleanNotificationConfig($record->avoid_to_clean);
                        $notificationTitle = $notificationConfig['title'] ?? null;

                        if (filled($notificationTitle)) {
                            $action->successNotification(fn (Notification $notification) => $notification
                                ->icon(FilamentIcon::resolve('inspirecms::warn'))
                                ->title($notificationTitle)
                                ->body($notificationConfig['body'] ?? null)
                                ->status($notificationConfig['status'] ?? null)
                            );
                        }
                        $action->success();
                    })
                    ->after(fn () => $this->dispatch('refresh')),
                TableAction::make('viewDifferences')
                    ->label(__('inspirecms::resources/content-version.buttons.view_differences.label'))
                    ->icon('heroicon-o-eye')
                    ->disabledForm()
                    ->modalFooterActions(fn () => []) // Disable footer actions
                    ->slideOver()
                    ->modalWidth(MaxWidth::ScreenTwoExtraLarge)
                    ->modalHeading(__('inspirecms::resources/content-version.buttons.view_differences.heading'))
                    ->modalDescription(fn ($record) => __('inspirecms::resources/content-version.buttons.view_differences.description', [
                        'author' => $record->author?->name ?? __('inspirecms::inspirecms.unknown_user'),
                        'date' => $record->created_at?->format('Y-m-d H:i:s') ?? __('inspirecms::inspirecms.n/a'),
                    ]))
                    ->modalContent(function (Model | ContentVersion $record) {
                        return view('inspirecms::filament.actions.content-history-detail', [
                            'record' => $record,
                            'diff' => collect($this->getDiffKeysFromRecord($record))
                                ->mapWithKeys(fn ($key) => [
                                    $key => $this->computeDiff(
                                        originalContent: $record->from_data[$key] ?? null, 
                                        newContent: $record->to_data[$key] ?? null
                                    ),
                                ])
                                ->all(),
                        ]);
                    }),
            ])
            ->bulkActions([
                TableBulkAction::make('bulkUpdateState')
                    ->color('gray')
                    ->label(__('inspirecms::resources/content-version.buttons.bulk_update_state.label'))
                    ->modalHeading(__('inspirecms::resources/content-version.buttons.bulk_update_state.heading'))
                    ->successNotificationTitle(__('inspirecms::resources/content-version.buttons.bulk_update_state.messages.success.title'))
                    ->failureNotificationTitle(__('inspirecms::resources/content-version.buttons.bulk_update_state.messages.failure.title'))
                    ->form([
                        Toggle::make('avoid_to_clean')
                            ->default(false)
                            ->columnSpanFull()
                            ->label('Avoid cleanup')
                            ->label(__('inspirecms::resources/content-version.avoid_to_clean.label'))
                            ->helperText(__('inspirecms::resources/content-version.avoid_to_clean.instructions')),
                    ])
                    ->action(function (TableBulkAction $action, $records, array $data) {
                        collect($records)->each(function ($record) use ($data) {
                            if (! $record instanceof Model || ! $record->exists) {
                                return;
                            }
                            if ($record instanceof ContentVersion) {
                                $record->avoid_to_clean = $data['avoid_to_clean'] ?? false;
                                $record->save();
                            }
                        });
                        $action->success();
                    })
                    ->after(fn () => $this->dispatch('refresh')),
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

    protected function configureTableAction(TableAction $action): void
    {
        parent::configureTableAction($action);

        $action
            ->hidden(function (?Model $record, $arguments) {
                if ($this->getOwnerRecord()->isLocked() || $this->getOwnerRecord()->trashed()) {
                    return true;
                }
                if (is_null($record)) {
                    return true;
                }
                return ! $record->exists && ! $record instanceof ContentVersion;
            });
    }

    protected function configureTableBulkAction(TableBulkAction $action): void
    {
        parent::configureTableBulkAction($action);

        $action
            ->hidden(function ($arguments) {
                return ($this->getOwnerRecord()->isLocked() || $this->getOwnerRecord()->trashed());
            });
    }

    protected function computeDiff($originalContent, $newContent)
    {
        try {
            if (is_array($newContent)) {
                return collect($newContent)
                    ->map(function ($item, $itemKey) use ($originalContent) {
                        $oldItem = $originalContent[$itemKey] ?? null;
                        return $this->computeDiff($oldItem, $item);
                    })
                    ->all();
            } elseif (!is_array($newContent) && is_array($originalContent)) {
                return collect($originalContent)
                    ->map(function ($item, $itemKey) use ($newContent) {
                        $newItem = $newContent[$itemKey] ?? null;
                        return $this->computeDiff($item, $newItem);
                    })
                    ->all();
            } else {

                // Format as "string" to compare diff
                if (is_null($originalContent)) {
                    $originalContent = '';
                }
                if (is_null($newContent)) {
                    $newContent = '';
                }

                return new Diff(
                    $originalContent,
                    $newContent
                );
            }
        } catch (\Exception $e) {
            return __('inspirecms::inspirecms.n/a');
        }
    } 

    protected function getDiffKeysFromRecord(Model | ContentVersion $record): array
    {
        return collect($record->to_data)
            ->keys()
            ->merge(array_keys($record->from_data))
            ->unique()
            ->all();
    }

    protected function getAvoidToCleanActionConfigFromRecord(Model | ContentVersion $record): array
    {
        $currentStateIsAvoidCleanup = boolval($record->avoid_to_clean);

        if ($currentStateIsAvoidCleanup) {
            // $label = 'Wait to cleanup';
            $label = __('inspirecms::resources/content-version.buttons.toggle_avoid_to_clean.true_label');
            $color = 'danger';
            $icon = 'heroicon-m-trash';
        } else {
            // $label = 'Avoid cleanup';
            $label = __('inspirecms::resources/content-version.buttons.toggle_avoid_to_clean.false_label');
            $color = 'gray';
            $icon = null;
        }

        return compact('label', 'color', 'icon');

    }

    protected function getAvoidToCleanNotificationConfig($newState)
    {
        if (!is_bool($newState)) {
            return [];
        }
        // Now: avoid to clean = true
        if ($newState) {
            // $title = 'Now avoiding to clean';
            $title = __('inspirecms::resources/content-version.buttons.toggle_avoid_to_clean.messages.avoid_cleanup.title');
            $body = __('inspirecms::resources/content-version.buttons.toggle_avoid_to_clean.messages.avoid_cleanup.body');
            $status = 'warning';
        } else {
            // $title = 'Now waiting to clean';
            $title = __('inspirecms::resources/content-version.buttons.toggle_avoid_to_clean.messages.wait_to_cleanup.title');
            $body = __('inspirecms::resources/content-version.buttons.toggle_avoid_to_clean.messages.wait_to_cleanup.body');
            $status = 'info';
        }
        return compact('title', 'body', 'status');
    }
}
