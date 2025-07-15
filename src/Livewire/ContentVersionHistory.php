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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Facades\ContentStatusManifest;
use SolutionForest\InspireCms\Licensing\LicenseTierAction;
use SolutionForest\InspireCms\Models\Contracts\Content;
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

    public function getTableRecordTitle(Model $record): ?string
    {
        return '#' . $record->getKey();
    }

    public function getOwnerRecord(): Model
    {
        return parent::getOwnerRecord()->loadMissing(['latestContentVersion']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query
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
                            TextColumn::make('id')
                                ->label(__('inspirecms::inspirecms.id'))
                                ->prefix('#')
                                ->weight('semibold'),
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
                            }),
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
                    ->action(function (Model | ContentVersion $record, TableAction $action) {

                        $record->avoid_to_clean = ! $record->avoid_to_clean;
                        $record->save();
                        $record->refresh();

                        $notificationConfig = $this->getAvoidToCleanNotificationConfig($record->avoid_to_clean);
                        $notificationTitle = $notificationConfig['title'] ?? null;

                        if (filled($notificationTitle)) {
                            $action->successNotification(
                                fn (Notification $notification) => $notification
                                    ->icon(FilamentIcon::resolve('inspirecms::warn'))
                                    ->title($notificationTitle)
                                    ->body($notificationConfig['body'] ?? null)
                                    ->status($notificationConfig['status'] ?? null)
                            );
                        }
                        $action->success();
                    })
                    ->after(fn () => $this->dispatch('refresh')),
                TableAction::make('rollbackToVersion')
                    ->label(__('inspirecms::resources/content-version.buttons.rollback.label'))
                    ->icon('heroicon-o-arrow-path')
                    ->authorize(function ($record) {
                        // Check via ContentPolicy
                        return Gate::check('rollbackVersion', [$this->getOwnerRecord(), $record]);
                    })
                    ->visible(function (Model | ContentVersion $record) {
                        // Check 1 - Can visible if is allow rollback on current license
                        if (! LicenseTierAction::RollbackContentVersion->isAllowed()) {
                            return false;
                        }

                        // Check 2 - Can visible if not a latest version
                        if ($this->getCurrentContentVersion()?->getKey() === $record->getKey()) {
                            return false;
                        }

                        return true;
                    })
                    ->action(function (Model | ContentVersion $record, TableAction $action) {
                        try {
                            DB::beginTransaction();
                            $this->rollbackVersion($record);
                            DB::commit();
                            $action->success();
                        } catch (\Throwable $th) {
                            DB::rollBack();
                            $action
                                ->failureNotification(function (Notification $notification) use ($th) {
                                    return $notification
                                        ->title(__('inspirecms::resources/content-version.buttons.rollback.messages.failure.title'))
                                        ->body(__('inspirecms::resources/content-version.buttons.rollback.messages.failure.body', [
                                            'details' => $th->getMessage(),
                                        ]));
                                })
                                ->failure();
                        }
                    }),
                TableAction::make('viewDifferences')
                    ->label(__('inspirecms::resources/content-version.buttons.view_differences.label'))
                    ->icon('heroicon-o-eye'),
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

        switch ($action->getName()) {
            case 'toggleAvoidToClean':
                $action
                    ->label(fn (Model | ContentVersion $record) => $this->getAvoidToCleanActionConfigFromRecord($record)['label'] ?? null)
                    ->color(fn (Model | ContentVersion $record): mixed => $this->getAvoidToCleanActionConfigFromRecord($record)['color'] ?? null)
                    ->icon(fn (Model | ContentVersion $record) => $this->getAvoidToCleanActionConfigFromRecord($record)['icon'] ?? null);

                break;
            case 'rollbackToVersion':
                $action->requiresConfirmation();

                foreach (['slideOver', 'modalWidth', 'modalHeading', 'modalDescription'] as $method) {
                    $action->{$method}(fn (Model | ContentVersion $record) => $this->getRollbackToVersionActionConfigFromRecord($record)[$method] ?? null);
                }
                foreach (['modalSubmitAction' => 'enableSubmitAction', 'modalCancelAction' => 'enableCancelAction'] as $method => $key) {
                    $action->{$method}(function ($record, $action) use ($key) {
                        $enableAction = $this->getRollbackToVersionActionConfigFromRecord($record)[$key] ?? false;
                        if (! $enableAction) {
                            return false;
                        }

                        return $action;
                    });
                }
                $action
                    ->successNotificationTitle(__('inspirecms::resources/content-version.buttons.rollback.messages.success.title'))
                    ->modalContent(function (Model | ContentVersion $record) {
                        $currentContentVersion = $this->getCurrentContentVersion();

                        if ($currentContentVersion && $currentContentVersion->getKey() === $record->getKey()) {
                            return str(__('inspirecms::inspirecms.n/a'))->toHtmlString();
                        }

                        $from = $this->getDiffItemDataForRollback($currentContentVersion);
                        $to = $this->getDiffItemDataForRollback($record);
                        $diff = collect(array_keys($from))->merge(array_keys($to))->unique()
                            ->mapWithKeys(fn ($key) => [
                                $key => $this->computeDiff($from[$key] ?? null, $to[$key] ?? null),
                            ])
                            ->all();

                        return view('inspirecms::filament.actions.content-history-detail', [
                            'diff' => $diff,
                        ]);
                    });

                break;
            case 'viewDifferences':
                $action
                    ->color('gray')
                    ->disabledForm()
                    // Disable footer actions
                    ->modalSubmitAction(false)->modalCancelAction(false)
                    ->slideOver()
                    ->modalWidth(MaxWidth::ScreenTwoExtraLarge)
                    ->modalHeading(fn (TableAction $action) => str(__('inspirecms::resources/content-version.buttons.view_differences.heading'))->when($action->getRecordTitle(), fn ($str, $value) => $str->finish(' - ' . $value)))
                    ->modalDescription(fn ($record) => __('inspirecms::resources/content-version.buttons.view_differences.description', [
                        'author' => $record->author?->name ?? __('inspirecms::inspirecms.unknown_user'),
                        'date' => $record->created_at?->format('Y-m-d H:i:s') ?? __('inspirecms::inspirecms.n/a'),
                    ]))
                    ->modalContent(function (Model | ContentVersion $record) {
                        $from = $this->mututeDiffItemDataForRecord($record, $record?->from_data ?? []);
                        $to = $this->mututeDiffItemDataForRecord($record, $record?->to_data ?? []);
                        $diff = collect(array_keys($from))->merge(array_keys($to))->unique()
                            ->mapWithKeys(fn ($key) => [
                                $key => $this->computeDiff($from[$key] ?? null, $to[$key] ?? null),
                            ])
                            ->all();

                        return view('inspirecms::filament.actions.content-history-detail', [
                            'diff' => $diff,
                        ]);
                    });

                break;
        }
    }

    protected function configureTableBulkAction(TableBulkAction $action): void
    {
        parent::configureTableBulkAction($action);

        $action
            ->hidden(function ($arguments) {
                return $this->getOwnerRecord()->isLocked() || $this->getOwnerRecord()->trashed();
            });
    }

    protected function getDiffItemDataForRollback(Model | ContentVersion $targetVersion): array
    {
        return $this->mututeDiffItemDataForRecord($targetVersion, $targetVersion?->to_data ?? []);
    }

    protected function mututeDiffItemDataForRecord(null | Model | ContentVersion $record, array $data): array
    {
        $propertyData = $data['propertyData'] ?? [];
        // Convert to array if it is a JSON string
        if (is_string($propertyData) && is_array(json_decode($propertyData, true))) {
            $propertyData = json_decode($propertyData, true);
        }

        return [
            'publish_state' => $record->publish_state,
            'avoid_to_clean' => $record->avoid_to_clean,
            // ...$data,
            'propertyData' => $propertyData,
        ];
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
            } elseif (! is_array($newContent) && is_array($originalContent)) {
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
        if (! is_bool($newState)) {
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

    protected function getRollbackToVersionActionConfigFromRecord(Model | ContentVersion $record): array
    {
        $currentContentVersion = $this->getCurrentContentVersion();

        if ($currentContentVersion && $currentContentVersion->getKey() === $record->getKey()) {
            $modalHeading = __('inspirecms::resources/content-version.buttons.rollback.invalid_heading');
            $modalDescription = __('inspirecms::resources/content-version.buttons.rollback.invalid_description');
            $modalWidth = MaxWidth::Medium;
            $slideOver = false;
            $enableSubmitAction = false;
            $enableCancelAction = false;
        } else {
            $modalHeading = __('inspirecms::resources/content-version.buttons.rollback.heading', [
                'from' => ($currentContentVersion->created_at?->format('Y-m-d H:i:s') ?? __('inspirecms::inspirecms.n/a')) . ' (' . $this->getTableRecordTitle($currentContentVersion) . ')',
                'to' => ($record->created_at?->format('Y-m-d H:i:s') ?? __('inspirecms::inspirecms.n/a')) . ' (' . $this->getTableRecordTitle($record) . ')',
            ]);
            $modalDescription = __('inspirecms::resources/content-version.buttons.rollback.description');
            $modalWidth = MaxWidth::ScreenTwoExtraLarge;
            $slideOver = true;
            $enableSubmitAction = true;
            $enableCancelAction = true;
        }

        return compact('modalHeading', 'modalDescription', 'modalWidth', 'enableSubmitAction', 'enableCancelAction', 'slideOver');
    }

    protected function getCurrentContentVersion(): null | Model | ContentVersion
    {
        return $this->getOwnerRecord()->latestContentVersion;
    }

    protected function rollbackVersion(Model | ContentVersion $targetVersion)
    {
        $modelCurrentContentVersion = $this->getCurrentContentVersion();
        /**
         * @var Model | Content
         */
        $modelContent = $this->getOwnerRecord();

        $from = $this->getDiffItemDataForRollback($modelCurrentContentVersion);
        $to = $this->getDiffItemDataForRollback($targetVersion);

        // // Content_Publish_Version
        // $publishableData = [];

        $locale = $modelContent->getLocale() ?? app()->getLocale();

        $toPublishState = $targetVersion->publish_state;
        $modelContent->setTranslation('propertyData', $locale, $from['propertyData'] ?? []);

        $modelContent->syncOriginal();
        $modelContent->setTranslation('propertyData', $locale, $to['propertyData'] ?? []);

        collect($to)->except(['propertyData', 'publish_state'])->each(function ($value, $key) use ($modelContent) {
            $modelContent->setPreloadVersionData($key, $value);
        });

        $modelContent->setPublishableState($toPublishState);
        $modelContent->setVersioningEvent('rollback');
        $modelContent->save();
    }
}
