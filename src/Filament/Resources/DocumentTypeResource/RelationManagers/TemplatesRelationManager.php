<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Pboivin\FilamentPeek\Pages\Concerns\HasBuilderPreview;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Factories\PreviewFactory;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Filament\Resources\Helpers\TemplateResourceHelper;
use SolutionForest\InspireCms\Filament\Tables\Actions\EditAndPreviewAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\SetAsDefaultAction;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplatesRelationManager extends RelationManager
{
    use CanAuthorizeRelationManager;
    use HasBuilderPreview {
        openPreviewModalForBuidler as protected traitOpenPreviewModalForBuidler;
    }
    use HasPreviewModal;

    protected static string $relationship = 'templates';

    protected static ?string $inverseRelationship = 'documentTypes';

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
            Forms\Components\Tabs::make()
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__('inspirecms::resources/template.editor.tabs.content'))
                        ->schema([
                            TemplateResourceHelper::getPageComponentInstructionsFormComponent(),
                            TemplateResourceHelper::getContentFormComponent('html_content'),
                        ]),
                    Forms\Components\Tabs\Tab::make(__('inspirecms::resources/template.editor.tabs.instructions'))
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
                    ->options(TemplateResourceHelper::getThemeSelectOptions())
                    ->view('inspirecms::filament.actions.select-action', [
                        'icon' => FilamentIcon::resolve('inspirecms::theme'),
                    ])
                    ->disabled(),
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->slideOver()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                SetAsDefaultAction::make()
                    ->color('primary')
                    ->button()
                    ->outlined()
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
                    Tables\Actions\DeleteAction::make(),
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

        $action->after(fn () => $this->refreshPageAlerts());
    }

    protected function configureDeleteAction(Tables\Actions\DeleteAction $action): void
    {
        parent::configureDeleteAction($action);

        $action->after(fn () => $this->refreshPageAlerts());
    }

    protected function configureDeleteBulkAction(Tables\Actions\DeleteBulkAction $action): void
    {
        parent::configureDeleteBulkAction($action);

        $action->after(fn () => $this->refreshPageAlerts());
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
                        ->title(__('inspirecms::messages.something_went_wrong'))
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

    // region Preview
    protected function getBuilderPreviewView(string $builderName): ?string
    {
        $templateRecord = $this->cachedMountedTableActionRecord;
        if (! $templateRecord || ! ($templateRecord instanceof Template)) {
            return null;
        }

        return $templateRecord->slug;   // wouldn't use this, but it's required
    }

    public function openPreviewModalForBuidler(string $builderName): void
    {
        $this->checkCustomListener();

        $editorData = $this->mutateInitialBuilderEditorData(
            $builderName,
            $this->prepareBuilderEditorData($builderName)
        );

        if (! isset($editorData['html_content']) || blank($editorData['html_content'])) {

            Notification::make()
                ->title(__('inspirecms::notification.template_not_found.title'))
                ->body(__('inspirecms::notification.template_not_found.body'))
                ->danger()
                ->seconds(60)
                ->send();

            // Avoid opening the modal if the template is not found
            return;
        }

        $this->dispatch(
            'openBuilderEditor',
            previewView: $this->getBuilderPreviewView($builderName),
            previewUrl: $this->getBuilderPreviewUrl($builderName),
            modalTitle: $this->getPreviewModalTitle(),
            editorTitle: $this->getBuilderEditorTitle(),
            editorData: $editorData,
            builderName: $builderName,
            pageClass: static::class,
        );
    }

    public function mutateInitialBuilderEditorData(string $builderName, array $editorData): array
    {
        /**
         * @var null | (Model & Template)
         */
        $templateRecord = $this->cachedMountedTableActionRecord;
        $theme = $this->theme ?? inspirecms_templates()->getCurrentTheme();
        $editorData['theme'] = $theme;
        $editorData['template'] = $templateRecord;
        $editorData['record_id'] = $templateRecord?->getKey(); // for update record content used

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
        return $previewData;
    }

    public static function renderBuilderPreview(string $view, array $data): string
    {
        $extraData = Arr::except($data, [
            'html_content',
            'document_type_id',
            'theme',
            'record_id',
        ]);

        return PreviewFactory::create()->renderTemplatePreview(
            templateContent: $data['html_content'] ?? '',
            documentType: $data['document_type_id'] ?? null,
            data: $extraData,
            theme: $data['theme'] ?? null,
        );
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
            ->title(__('inspirecms::buttons.edit_and_preview.messages.success.title'))
            ->success()
            ->send();
    }

    protected function getBuilderEditorTitle(): string
    {
        return __('inspirecms::resources/template.editor.title');
    }
    // endregion Preview

    // region Helpers
    protected function refreshPageAlerts(): void
    {
        $this->dispatch('refreshAlerts');
    }

    protected function assignDefaultTemplateIfNotSet($template): void
    {
        inspirecms_templates()->assignDefaultTemplateIfNotSet($this->getOwnerRecord(), $template);
    }
    // endregion Helpers
}
