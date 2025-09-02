<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\Templates\Actions\ThemeSelector;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\TemplateBasicForm;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\TemplateSimpleEditorForm;
use SolutionForest\InspireCms\Filament\Resources\Templates\Tables\Columns\TemplateIsDefaultColumn;
use SolutionForest\InspireCms\Filament\Resources\Templates\Tables\Columns\TemplateSlugColumn;
use SolutionForest\InspireCms\Filament\Resources\Templates\Tables\TemplatesTable;
use SolutionForest\InspireCms\Filament\Tables\Actions\EditAndPreviewAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\SetAsDefaultAction;
use SolutionForest\InspireCms\Models\Contracts\Template;
use Throwable;

class TemplatesAssociationTable
{
    public static function configure(Table $table): Table
    {
        return TemplatesTable::configure($table)
            ->recordAction('editAndPreview')
            ->description(fn () => __('inspirecms::resources/document-type.templates.description'))
            ->columns([
                TemplateSlugColumn::make(),
                TemplateIsDefaultColumn::make(),
            ])
            ->headerActions([

                ThemeSelector::make()
                    ->disabled(),

                CreateAction::make()
                    ->createAnother(false)
                    ->after(function (Model $record, RelationManager $livewire) {
                        // Set the default template if it's not set
                        inspirecms_templates()->assignDefaultTemplateIfNotSet($livewire->getOwnerRecord(), $record);
                        $livewire->dispatch('refreshAlerts');
                    }),
                AttachAction::make()
                    ->slideOver()
                    ->preloadRecordSelect()
                    ->multiple()
                    ->after(function (array $data, ?Model $record, RelationManager $livewire) {
                        if (is_null($record)) {
                            $record = is_array($data['recordId'] ?? []) ? collect($data['recordId'] ?? [])->first() : $data['recordId'];
                        }
                        // Set the default template if it's not set
                        inspirecms_templates()->assignDefaultTemplateIfNotSet($livewire->getOwnerRecord(), $record);
                        $livewire->dispatch('refreshAlerts');
                    }),
            ])
            ->recordActions([

                SetAsDefaultAction::make()
                    ->color('primary')
                    ->button()
                    ->outlined()
                    ->action(function (Template $record, Action $action, RelationManager $livewire) {
                        $livewire->getOwnerRecord()->setAsDefaultTemplate($record);

                        $action->success();

                        $livewire->dispatch('$refresh');
                    }),

                EditAction::make('rename')
                    ->label(__('inspirecms::buttons.rename.label'))
                    ->icon(FilamentIcon::resolve('inspirecms::edit.simple'))
                    ->successNotificationTitle(__('inspirecms::buttons.rename.messages.success.title'))
                    ->failureNotificationTitle(__('inspirecms::buttons.rename.messages.failure.title'))
                    ->link()
                    ->schema(fn (Schema $schema) => TemplateBasicForm::configure($schema)),

                ActionGroup::make([
                    EditAndPreviewAction::make()
                        ->builderName('templateViewBuilder'),

                    static::configureViewOrEditActionForPeekEditor(
                        EditAction::make(),
                    ),
                    static::configureViewOrEditActionForPeekEditor(
                        ViewAction::make(),
                    ),

                    DetachAction::make()
                        ->after(fn (RelationManager $livewire) => $livewire->dispatch('refreshAlerts')),

                    DeleteAction::make()
                        ->after(fn (RelationManager $livewire) => $livewire->dispatch('refreshAlerts')),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->after(fn (RelationManager $livewire) => $livewire->dispatch('refreshAlerts')),
                    DeleteBulkAction::make()
                        ->after(fn (RelationManager $livewire) => $livewire->dispatch('refreshAlerts')),
                ])->iconButton(),
            ]);
    }

    protected static function configureViewOrEditActionForPeekEditor(Action $action)
    {
        $action
            ->recordTitle(fn (Template $record) => $record->slug)
            ->modalWidth('7xl')
            ->slideOver()
            ->beforeFormFilled(function (Model | Template $record, Action $action, $livewire) {
                try {
                    $record->initializeTemplate($livewire->theme);
                    $record->save();
                } catch (Throwable $th) {
                    Notification::make()
                        ->title(__('inspirecms::messages.something_went_wrong'))
                        ->body($th->getMessage())
                        ->danger()
                        ->send();

                    $action->cancel();
                }
            })
            ->schema(fn (Schema $schema) => TemplateSimpleEditorForm::configure($schema))
            ->mutateRecordDataUsing(function (Template $record, $livewire) {
                return [
                    'theme' => $livewire->theme,
                    'content' => $record->getContent(theme: $livewire->theme),
                ];
            });

        if ($action instanceof EditAction) {
            $action->using(function (array $data, Model | Template $record, $livewire) {
                $record->updateContent($data['content'], $livewire->theme);
            });
        }

        return $action;
    }
}
