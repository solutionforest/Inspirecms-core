<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Pboivin\FilamentPeek\Pages\Concerns\HasBuilderPreview;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use Pboivin\FilamentPeek\Support\Html;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

trait ContentPreviewEditorTrait
{
    use HasBuilderPreview;
    use HasPreviewModal;

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

    public static function getBuilderEditorSchema(string $builderName): \Filament\Forms\Components\Component | array
    {
        $resource = static::getResource();

        return $resource::getPreviewBuilderEditorSchema($builderName);
    }

    public function mutateInitialBuilderEditorData(string $builderName, array $editorData): array
    {
        $contentModel = $this->getModel();
        $editorData['contentModel'] = $contentModel;

        $documentType = $this->getDocumentType();
        $editorData['documentType'] = $documentType instanceof Model ? $documentType->getKey() : $documentType;

        if ($this instanceof CreateRecord) {
            $editorData['operation'] = 'create';
            $editorData['contentData'] = $this->data;
            $editorData['contentData']['children'] = [];
        } else {
            $editorData['operation'] = 'edit';

            $content = $this->getRecord();
            $editorData['contentData'] = $this->data;
            $editorData['contentData']['children'] = $content->children()->pluck($content->getKeyName())->toArray();
            $editorData['contentKey'] = $content->getKey();

        }

        return $editorData;
    }

    public static function mutateBuilderPreviewData(string $builderName, array $editorData, array $previewData): array
    {
        $contentModel = $editorData['contentModel'];

        if (! in_array(Content::class, class_implements($contentModel))) {
            throw new \Exception('Model must implement ' . Content::class);
        }

        $documentType = InspireCmsConfig::getDocumentTypeModelClass()::find($editorData['documentType']);

        $contentDto = $contentModel::toPreviewDto(
            record: $editorData['contentData'],
            propertyData: $editorData['propertyData'] ?? [],
            locale: $editorData['activeLocale'],
            documentType: $documentType,
        );

        $previewData['content'] = $contentDto;

        return $previewData;
    }
}
