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
use Pboivin\FilamentPeek\Pages\Concerns\HasBuilderPreview;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use Pboivin\FilamentPeek\Support\Html;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Filament\Resources\Helpers\TemplateResourceHelper;
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

    public ?string $theme = null;

    public function mount(): void
    {
        parent::mount();

        $this->theme = inspirecms_templates()->getCurrentTheme();
    }

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
                TemplateResourceHelper::getSlugFormComponent(),
            ]);
    }

    public static function getBuilderEditorSchema(string $builderName): \Filament\Forms\Components\Component | array
    {
        return [
            Forms\Components\Hidden::make('record_id'),
            Forms\Components\Hidden::make('document_type_id'),
            TemplateResourceHelper::getThemeFormComponent()->disabled(),
            // todo: add to translation
            Forms\Components\Tabs::make()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Content')
                        ->schema([
                            TemplateResourceHelper::getPageComponentInstructionsFormComponent(),
                            TemplateResourceHelper::getContentFormComponent('html_content'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Instructions')
                        ->schema([
                            TemplateResourceHelper::getPropertyTypeInstructionsFormComponent(),
                        ]),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn ($record) => $record->slug)
            ->recordAction('editAndPreview')
            ->modelLabel(fn () => __('inspirecms::inspirecms.template'))
            ->description(fn () => __('inspirecms::resources/document-type.templates.description'))
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('inspirecms::resources/template.slug.label'))
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('inspirecms::resources/template.is_default.label'))
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\SelectAction::make('theme')
                    ->options(inspirecms_templates()->getAvailableThemes())
                    ->view('inspirecms::filament.actions.select-action', [
                        'icon' => 'heroicon-o-paint-brush',
                    ]),
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
                    ->successNotificationTitle(__('inspirecms::actions.set_as_default.notification.saved.title'))
                    ->authorize(static fn (RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record))
                    ->action(function (Template $record, Tables\Actions\Action $action) {

                        $this->getOwnerRecord()->setAsDefaultTemplate($record);

                        $action->success();

                        $this->dispatch('$refresh');
                    }),
                Tables\Actions\ActionGroup::make([
                    EditAndPreviewAction::make()->builderName('templateViewBuilder')->authorize(static fn (RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record)),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DetachAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::resources/document-type.templates.label');
    }

    protected function configureCreateAction(CreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action
            ->form(fn (Form $form): Form => $this->createForm($form->columns(2)))
            ->createAnother(false)
            // Set the default template if it's not set
            ->after(function (Model $record) {
                $this->assignDefaultTemplateIfNotSet($record);
                $this->refreshPageAlerts();
            });
    }

    protected function configureAttachAction(Tables\Actions\AttachAction $action): void
    {
        parent::configureAttachAction($action);

        $action
            ->multiple()
            // Set the default template if it's not set
            ->after(function (array $data, ?Model $record) {
                if (is_null($record)) {
                    $record = is_array($data['recordId'] ?? []) ? collect($data['recordId'] ?? [])->first() : $data['recordId'];
                }
                $this->assignDefaultTemplateIfNotSet($record);
                $this->refreshPageAlerts();
            });
    }

    protected function configureDetachAction(Tables\Actions\DetachAction $action): void
    {
        parent::configureDetachAction($action);

        $action->after(fn ($record) => $this->refreshPageAlerts());
    }

    protected function configureDeleteBulkAction(Tables\Actions\DeleteBulkAction $action): void
    {
        parent::configureDeleteBulkAction($action);

        $action->after(fn ($record) => $this->refreshPageAlerts());
    }

    protected function configureViewAction(Tables\Actions\ViewAction $action): void
    {
        parent::configureViewAction($action);

        $this->configureViewOrEditAction($action);
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction($action);

        $this->configureViewOrEditAction($action);
    }

    protected function configureViewOrEditAction(Tables\Actions\Action $action)
    {
        $action
            ->recordTitle(fn (Template $record) => $record->slug)
            ->modalWidth('7xl')
            ->slideOver()
            ->beforeFormFilled(function (Model | Template $record, Tables\Actions\Action $action) {
                try {
                    $record->initializeTemplate($this->theme);
                    $record->save();
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title(__('inspirecms::notification.something_went_wrong.title'))
                        ->body($th->getMessage())
                        ->danger()
                        ->send();
        
                    $action->cancel();
                }
            })
            ->form([
                TemplateResourceHelper::getThemeFormComponent()->disabled(),
                TemplateResourceHelper::getContentFormComponent()->hiddenLabel(),
            ])
            ->mutateRecordDataUsing(function (Template $record) {
                return [
                    'theme' => $this->theme,
                    'content' => $record->getContent(theme: $this->theme),
                ];
            });

        if ($action instanceof Tables\Actions\EditAction) {
            $action->using(function (array $data, Model | Template $record) {
                $record->updateContent($data['content'], $this->theme);
            });
        }
    }

    //region Preview
    protected function getBuilderPreviewView(string $builderName): ?string
    {
        $templateRecord = $this->cachedMountedTableActionRecord;
        if (! $templateRecord || ! ($templateRecord instanceof Template)) {
            return null;
        }

        return $templateRecord->slug;   // wouldn't use this, but it's required
    }

    public static function renderBuilderPreview(string $view, array $data): string
    {
        $htmlContent = $data['html_content'] ?? '';

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
        /**
         * @var null | (Model & Template)
         */
        $templateRecord = $this->cachedMountedTableActionRecord;
        $theme = $this->theme?? inspirecms_templates()->getCurrentTheme();
        $editorData['theme'] = $theme;
        $editorData['record_id'] = $templateRecord?->getKey();
        $editorData['html_content'] = $templateRecord?->getContent($theme);

        $documentType = $this->getOwnerRecord();
        $editorData['document_type_id'] = $documentType->getKey();
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
        $documentTypeId = $editorData['document_type_id'] ?? null;
        $previewData['documentType'] = filled($documentTypeId)
            ? InspireCmsConfig::getDocumentTypeModelClass()::with('fields')->find($documentTypeId)
            : null;

        return $previewData;
    }

    public function updateBuilderFieldWithEditorData(string $builderName, array $editorData): void
    {
        $htmlContent = $editorData['html_content'] ?? '';
        $theme = $editorData['theme'] ?? $this->theme ?? inspirecms_templates()->getCurrentTheme();
        $templateId = $editorData['record_id'] ?? null;
        if (! $templateId) {
            return;
        }

        $template = $this->getRelationship()->getRelated()->find($templateId);
        if (! $template) {
            return;
        }

        $template->updateContent($htmlContent, $theme);

        Notification::make()
            ->title(__('inspirecms::actions.edit_and_preview.notification.saved.title'))
            ->success()
            ->send();
    }

    protected function getBuilderEditorTitle(): string
    {
        return __('inspirecms::resources/template.editor.title');
    }
    //endregion Preview

    //region Helpers
    protected function refreshPageAlerts(): void
    {
        $this->dispatch('refreshAlerts');
    }

    protected function assignDefaultTemplateIfNotSet($template): void
    {
        inspirecms_templates()->assignDefaultTemplateIfNotSet($this->getOwnerRecord(), $template);
    }
    //endregion Helpers
}
