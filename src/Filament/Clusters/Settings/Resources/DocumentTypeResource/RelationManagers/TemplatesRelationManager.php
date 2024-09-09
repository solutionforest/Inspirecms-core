<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Riodwanto\FilamentAceEditor\AceEditor;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'templates';

    public function createForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('inspirecms::inspirecms.name'))
                    ->inlineLabel()
                    ->required()
                    ->afterStateUpdated(fn ($state) => Str::slug($state, '_')),
            ]);
    }

    public function editForm(Form $form): Form
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
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('name')
                        ->weight('bold')
                        ->description(fn (Template $record) => $record->path),
                    Tables\Columns\IconColumn::make('is_default')
                        ->tooltip(__('inspirecms::inspirecms.is_default'))
                        ->boolean(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('set_as_default')
                    ->label(__('inspirecms::inspirecms.set_as_default'))
                    ->color('secondary')
                    ->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'))
                    ->action(function (Template $record, Tables\Actions\Action $action) {

                        $this->getOwnerRecord()->setAsDefaultTemplate($record);

                        $action->success();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    protected function configureCreateAction(CreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action
            ->form(fn (Form $form): Form => $this->createForm($form->columns(2)))
            ->slideOver();
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction($action);

        $action
            ->form(fn (Form $form): Form => $this->editForm($form->columns(2)))
            ->recordTitle(fn (Template $record) => $record->path)
            ->modalWidth('7xl')
            ->slideOver()
            ->beforeFormFilled(function (Template $record, Tables\Actions\Action $action) {
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
            });
    }
}
