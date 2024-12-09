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
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Pboivin\FilamentPeek\Pages\Concerns\HasBuilderPreview;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use Pboivin\FilamentPeek\Support\Html;
use Riodwanto\FilamentAceEditor\AceEditor;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Filament\Tables\Actions\EditAndPreviewAction;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplatesRelationManager extends RelationManager
{
    use CanAuthorizeRelationManager;
    use HasBuilderPreview;
    use HasPreviewModal;

    protected static string $relationship = 'templates';

    /**
     * @var array An array to store preview editor data.
     */
    public $data = [];

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $ownerRecord->canManageTemplates();
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
                static::getContentFormField()
                    ->afterStateHydrated(fn ($component, Template $record) => $component->state(static::getTemplateContent($record)))
                    ->dehydrateStateUsing(fn ($state, Template $record) => static::updateTemplateContent($record, $state)),
            ]);
    }

    /** @return Forms\Components\Component|Forms\Components\Field */
    public static function getContentFormField($name = 'content')
    {
        return AceEditor::make($name)
            ->mode('php')
            ->darkTheme('tomorrow_night_eighties')
            ->height('64rem');
    }

    public static function getBuilderEditorSchema(string $builderName): \Filament\Forms\Components\Component | array
    {
        return [
            Forms\Components\ViewField::make('property_type_instructions')
                ->view('inspirecms::filament.forms.components.property-type-instructions'),
            static::getContentFormField('htmlContent'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->contentGrid(['xl' => 3, 'lg' => 2, 'default' => 1])
            ->recordTitle(fn ($record) => $record->path)
            ->modelLabel(fn () => __('inspirecms::resources/document-type.templates.singular'))
            ->pluralModelLabel(__('inspirecms::resources/document-type.templates.plural'))
            ->description(fn () => __('inspirecms::resources/document-type.templates.description'))
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('slug')
                        ->weight('bold')
                        ->description(fn (Template $record) => $record->path)
                        ->grow(),
                    Tables\Columns\TextColumn::make('is_default')
                        ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                        ->iconColor(fn ($state) => $state ? 'success' : 'danger')
                        ->formatStateUsing(fn () => __('inspirecms::inspirecms.is_default'))
                        ->extraAttributes(['class' => 'sdsds'])
                        ->grow(),
                ])->from('md'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\Action::make('set_as_default')
                    ->label(__('inspirecms::actions.set_as_default.label'))
                    ->color('primary')
                    ->icon('heroicon-o-check-circle')
                    ->button()
                    ->outlined()
                    ->successNotificationTitle(__('inspirecms::actions.set_as_default.notifications.saved.title'))
                    ->authorize(static fn (RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record))
                    ->action(function (Template $record, Tables\Actions\Action $action) {

                        $this->getOwnerRecord()->setAsDefaultTemplate($record);

                        $action->success();

                        $this->dispatch('$refresh');
                    }),
                Tables\Actions\ActionGroup::make([
                    EditAndPreviewAction::make()->builderName('templateViewBuilder'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DetachAction::make(),
                ]),
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

    protected function configureTableAction(Tables\Actions\Action $action): void
    {
        parent::configureTableAction($action);

        if ($action instanceof EditAndPreviewAction) {
            $action->authorize(static fn (RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record));
        }
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
                $this->dispatch('refreshAlerts');
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

    protected function getBuilderPreviewView(string $builderName): ?string
    {
        $templateRecord = $this->cachedMountedTableActionRecord;
        if (! $templateRecord || ! ($templateRecord instanceof Template)) {
            return null;
        }

        return $templateRecord->getViewFullName();
    }

    public static function renderBuilderPreview(string $view, array $data): string
    {
        $htmlContent = $data['htmlContent'] ?? '';

        $documentType = $data['documentType'] ?? null;

        $dummyDto = \SolutionForest\InspireCms\Dtos\ContentDto::fakeForDocumentType($documentType);

        return Html::injectPreviewModalStyle(
            Blade::render($htmlContent, [
                'content' => $dummyDto,
            ])
        );
    }

    public function mutateInitialBuilderEditorData(string $builderName, array $editorData): array
    {
        $templateRecord = $this->cachedMountedTableActionRecord;
        $editorData['recordId'] = $templateRecord?->getKey();
        if ($templateRecord instanceof Template) {
            $editorData['htmlContent'] = static::getTemplateContent($templateRecord);
        }
        $documentType = $this->getOwnerRecord();
        $editorData['documentTypeId'] = $documentType->getKey();
        $editorData['property_type_instructions'] = collect($documentType?->fields)
            ->map(fn ($field) => [
                'dtoData' => $field->toDto(),
                'fieldType' => $field->type,
            ])
            ->all();

        return $editorData;
    }

    public static function mutateBuilderPreviewData(string $builderName, array $editorData, array $previewData): array
    {
        $documentTypeId = $editorData['documentTypeId'] ?? null;
        $previewData['documentType'] = filled($documentTypeId)
            ? InspireCmsConfig::getDocumentTypeModelClass()::with('fields')->find($documentTypeId)
            : null;

        return $previewData;
    }

    public function updateBuilderFieldWithEditorData(string $builderName, array $editorData): void
    {
        $htmlContent = $editorData['htmlContent'] ?? '';
        $templateId = $editorData['recordId'] ?? null;
        if (! $templateId) {
            return;
        }

        $template = $this->getRelationship()->getRelated()->find($templateId);
        if (! $template) {
            return;
        }

        static::updateTemplateContent($template, $htmlContent);

        Notification::make()
            ->title(__('inspirecms::actions.edit_and_preview.notifications.saved.title'))
            ->success()
            ->send();
    }

    /**
     * Retrieves the content of the template associated with the given record.
     *
     * @param  Template  $record  The record from which to retrieve the template content.
     * @return string The content of the template.
     */
    protected static function getTemplateContent($record): string
    {
        return file_get_contents($record->getFileFullPath());
    }

    /**
     * Updates the content of a template.
     *
     * @param  Template  $record  The record associated with the template.
     * @param  string  $content  The new content to be updated in the template.
     */
    protected static function updateTemplateContent($record, $content): void
    {
        file_put_contents($record->getFileFullPath(), $content);
    }
}
