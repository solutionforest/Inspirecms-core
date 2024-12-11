<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

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

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                TemplateResourceHelper::getPageComponentInstructionsFormComponent(),
                TemplateResourceHelper::getContentFormComponent()
                    ->afterStateHydrated(fn ($component, Template $record) => $component->state(TemplateResourceHelper::getViewContent($record)))
                    ->dehydrateStateUsing(fn ($state, Template $record) => TemplateResourceHelper::updateViewContent($record, $state)),
            ]);
    }

    public static function getBuilderEditorSchema(string $builderName): \Filament\Forms\Components\Component | array
    {
        return [
            TemplateResourceHelper::getPropertyTypeInstructionsFormComponent(),
            TemplateResourceHelper::getPageComponentInstructionsFormComponent(),
            TemplateResourceHelper::getContentFormComponent('htmlContent'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->contentGrid(['xl' => 3, 'lg' => 2, 'default' => 1])
            ->recordTitle(fn ($record) => $record->path)
            ->modelLabel(fn () => __('inspirecms::inspirecms.template'))
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
                    ->successNotificationTitle(__('inspirecms::actions.set_as_default.notification.saved.title'))
                    ->authorize(static fn (RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record))
                    ->action(function (Template $record, Tables\Actions\Action $action) {

                        $this->getOwnerRecord()->setAsDefaultTemplate($record);

                        $action->success();

                        $this->dispatch('$refresh');
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    EditAndPreviewAction::make()->builderName('templateViewBuilder'),
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
                TemplateResourceHelper::setDefaultTemplateIfEmpty($this->getOwnerRecord(), $record);
                $this->dispatch('refreshAlerts');
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
                TemplateResourceHelper::setDefaultTemplateIfEmpty($this->getOwnerRecord(), $record);
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
                ->title(__('inspirecms::notification.something_went_wrong.title'))
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
            $editorData['htmlContent'] = TemplateResourceHelper::getViewContent($templateRecord);
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

        TemplateResourceHelper::updateViewContent($template, $htmlContent);

        Notification::make()
            ->title(__('inspirecms::actions.edit_and_preview.notification.saved.title'))
            ->success()
            ->send();
    }
}
