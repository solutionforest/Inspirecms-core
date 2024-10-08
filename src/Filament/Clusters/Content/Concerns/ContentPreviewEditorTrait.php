<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Pboivin\FilamentPeek\Pages\Concerns\HasBuilderPreview;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use Pboivin\FilamentPeek\Support\Html;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Dtos\DocumentTypeDto;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait ContentPreviewEditorTrait
{
    use HasPreviewModal;
    use HasBuilderPreview;
    
    protected function getBuilderPreviewView(string $builderName): ?string
    {
        $template = filled($this->data['template_id'] ?? null) ? InspireCmsConfig::getTemplateModelClass()::find($this->data['template_id']) : null;
        $template ??= $this->getDocumentType()?->getDefaultTemplate();
        if (! $template) {
            Notification::make()
                ->title(__('inspirecms::notification.template_file_not_found.title'))
                ->body(__('inspirecms::notification.template_file_not_found.body'))
                ->danger()
                ->send();

            throw new Halt;
        }

        return $template->getViewFullName();
    }

    public static function getBuilderEditorSchema(string $builderName): \Filament\Forms\Components\Component|array
    {
        $resource = static::getResource();

        return $resource::getPreviewBuilderEditorSchema($builderName);
    }
    
    public function mutateInitialBuilderEditorData(string $builderName, array $editorData): array
    {
        $documentType = $this->getDocumentType();
        $editorData['documentType'] = $documentType instanceof Model ? $documentType->getKey() : $documentType;

        if ($this instanceof CreateRecord) {
            $editorData['operation'] = 'create';
            $editorData['contentData'] = $this->data;
        } else {
            $editorData['operation'] = 'edit';

            $content = $this->getRecord();
            $editorData['contentKey'] = $content->getKey();

        }
    
        return $editorData;
    }

    public static function mutateBuilderPreviewData(string $builderName, array $editorData, array $previewData): array
    {
        if ($editorData['operation'] === 'create') {
            /**
             * @var ContentDto
             */
            $contentDto = ContentDto::fromArray($editorData['contentData']);
            $documentType = InspireCmsConfig::getDocumentTypeModelClass()::find($editorData['documentType']);
            if (! $documentType) {
                $contentDto->documentType = DocumentTypeDto::fromModel($documentType);
            }

        } else {

            $contentKey = $editorData['contentKey'];
            $content = static::getResource()::resolveRecordRouteBinding($contentKey);
            /**
             * @var ContentDto
             */
            $contentDto = ContentDto::fromArray($content->attributesToArray())
                ->setLocale($editorData['activeLocale'])
                ->setFallbackLocale($content->getFallbackLocale())
                ->setModel($content);
            $contentDto->documentType = DocumentTypeDto::fromModel($content->documentType);
        }

        $contentDto->setPropertyData($editorData['propertyData'] ?? []);

        $previewData['content'] = $contentDto;
        return $previewData;
    }
    
    public static function renderBuilderPreview(string $view, array $data): string
    {   
        return Html::injectPreviewModalStyle(
            view('inspirecms::filament-peek.preview', [
                'templateData' => $data,
                'templateView' => $view,
            ])->render()
        );
    }
}
