<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Pboivin\FilamentPeek\Pages\Concerns\HasBuilderPreview;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Factories\PreviewFactory;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Tables\TemplatesAssociationTable;
use SolutionForest\InspireCms\Filament\Resources\TemplateResource;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\TemplateBasicForm;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\TemplatePeekEditorForm;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\TemplateSimpleEditorForm;
use SolutionForest\InspireCms\Filament\Tables\Actions\EditAndPreviewAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\SetAsDefaultAction;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template;

class TemplatesRelationManager extends RelationManager
{
    use CanAuthorizeRelationManager;
    use HasBuilderPreview;
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

    public static function getRelatedResource(): ?string
    {
        return InspireCmsConfig::getFilamentResource('template', TemplateResource::class);
    }

    public function form(Schema $schema): Schema
    {
        if (in_array($schema->getOperation(), ['create'])) {
            return TemplateBasicForm::configure($schema);
        }

        return TemplateSimpleEditorForm::configure($schema);
    }

    public static function getBuilderEditorSchema(string $builderName): Component | array
    {
        return TemplatePeekEditorForm::configure(Schema::make())->getComponents(true, true);
    }

    public function table(Table $table): Table
    {
        return TemplatesAssociationTable::configure($table);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::resources/document-type.templates.label');
    }

    public function getDefaultActionAuthorizationResponse(Action $action): ?Response
    {
        if ($action instanceof AttachAction) {
            return $this->isReadOnly() ? Response::deny() : $this->getAttachAuthorizationResponse();
        } elseif ($action instanceof DetachAction) {
            return $this->isReadOnly() ? Response::deny() : $this->getDetachAuthorizationResponse($action->getRecord());
        } elseif ($action instanceof SetAsDefaultAction || $action instanceof EditAndPreviewAction) {
            return $this->isReadOnly() ? Response::deny() : $this->getEditAuthorizationResponse($action->getRecord());
        }

        return parent::getDefaultActionAuthorizationResponse($action);
    }

    // region Preview
    protected function getBuilderPreviewView(string $builderName): ?string
    {
        return 'handle by previewFactory';
    }

    public function mutateInitialBuilderEditorData(string $builderName, array $editorData): array
    {
        $recordKey = collect($this->mountedActions)->pluck('context.recordKey')->first();
        /**
         * @var null | (Model & Template)
         */
        $templateRecord = $recordKey ? $this->getTable()->getQuery()->find($recordKey) : null;
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

    public static function renderPreviewModalView(string $view, array $data): string
    {
        return static::renderBuilderPreview($view, $data);
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
}
