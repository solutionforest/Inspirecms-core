<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Riodwanto\FilamentAceEditor\AceEditor;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplatesRelationManager extends RelationManager
{
    use CanAuthorizeRelationManager;

    protected static string $relationship = 'templates';

    protected static ?string $icon = 'heroicon-c-sparkles';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        if (is_null($ownerRecord->templates_count)) {
            $ownerRecord->loadCount('templates');
        }

        return $ownerRecord->templates_count;
    }

    public function createForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('slug')
                    ->label(__('inspirecms::inspirecms.slug'))
                    ->inlineLabel()
                    ->required()
                    ->maxLength(255)
                    ->live(true, 500)
                    ->afterStateUpdated(fn ($component, ?string $state) => $component->state(Str::slug($state)))
                    ->unique(
                        table: $this->getRelationship()->getRelated()->getTable(),
                        column: 'slug',
                        ignoreRecord: true
                    ),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                AceEditor::make('content')
                    ->mode('php')
                    ->theme('github')
                    ->darkTheme('dracula')
                    ->afterStateHydrated(fn ($component, Template $record) => $component->state(file_get_contents($record->getFileFullPath())))
                    ->dehydrateStateUsing(fn ($state, Template $record) => file_put_contents($record->getFileFullPath(), $state)),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->contentGrid(['lg' => 3, 'md' => 2])
            ->recordTitle(fn ($record) => $record->path)
            ->modelLabel(fn () => __('inspirecms::resources/document-type.templates.singular'))
            ->pluralModelLabel(__('inspirecms::resources/document-type.templates.plural'))
            ->description(fn () => __('inspirecms::resources/document-type.templates.description'))
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('slug')
                        ->weight('bold')
                        ->description(fn (Template $record) => $record->path),
                    Tables\Columns\TextColumn::make('is_default')
                        ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                        ->iconColor(fn ($state) => $state ? 'success' : 'danger')
                        ->formatStateUsing(fn () => __('inspirecms::inspirecms.is_default')),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\Action::make('set_as_default')
                    ->label(__('inspirecms::inspirecms.set_as_default'))
                    ->color('secondary')
                    ->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'))
                    ->authorize(static fn (RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record))
                    ->action(function (Template $record, Tables\Actions\Action $action) {

                        $this->getOwnerRecord()->setAsDefaultTemplate($record);

                        $action->success();

                        $this->dispatch('$refresh');
                    }),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::resources/document-type.templates.plural');
    }

    protected function configureCreateAction(CreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action
            ->form(fn (Form $form): Form => $this->createForm($form->columns(2)))
            ->createAnother(false)
            // Set the default template if it's not set
            ->after(function (Model $record) {
                if (is_null($this->getOwnerRecord()->getDefaultTemplate())) {
                    $this->getOwnerRecord()->setAsDefaultTemplate($record);
                }
            });
    }

    protected function configureAttachAction(Tables\Actions\AttachAction $action): void
    {
        parent::configureAttachAction($action);

        $action
            // Set the default template if it's not set
            ->after(function (Model $record) {
                if (is_null($this->getOwnerRecord()->getDefaultTemplate())) {
                    $this->getOwnerRecord()->setAsDefaultTemplate($record);
                }
                $this->dispatch('refreshAlerts');
            });
    }

    protected function configureDetachAction(Tables\Actions\DetachAction $action): void
    {
        parent::configureDetachAction($action);

        $action
            ->after(function (Model $record) {
                $this->dispatch('refreshAlerts');
            });
    }

    protected function configureDeleteBulkAction(Tables\Actions\DeleteBulkAction $action): void
    {
        parent::configureDeleteBulkAction($action);

        $action
            ->after(function () {
                $this->dispatch('refreshAlerts');
            });
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction($action);

        $action
            ->recordTitle(fn (Template $record) => $record->path)
            ->modalWidth('7xl')
            ->slideOver()
            ->beforeFormFilled(fn (Template $record, Tables\Actions\Action $action) => $this->configureTemplateForm($record, $action));
    }

    protected function configureViewAction(Tables\Actions\ViewAction $action): void
    {
        parent::configureViewAction($action);

        $action
            ->recordTitle(fn (Template $record) => $record->path)
            ->modalWidth('7xl')
            ->slideOver()
            ->hidden(fn ($record) => $this->canEdit($record))
            ->beforeFormFilled(fn (Template $record, Tables\Actions\Action $action) => $this->configureTemplateForm($record, $action));
    }

    protected function configureTemplateForm(Template $record, Tables\Actions\Action $action)
    {
        try {
            if (! $record->isFileCreated()) {
                $record->createTemplateFile();
            }
        } catch (\Throwable $th) {
            Notification::make()
                ->title(__('inspirecms::inspirecms.something_went_wrong'))
                ->body($th->getMessage())
                ->danger()
                ->send();

            $action->cancel();
        }
    }
}
