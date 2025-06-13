<?php

namespace SolutionForest\InspireCms\Base\Filament\Concerns;

use Filament\Forms\Components\Component;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Pboivin\FilamentPeek\Pages\Concerns\HasBuilderPreview;
use Pboivin\FilamentPeek\Pages\Concerns\HasPreviewModal;
use SolutionForest\InspireCms\Factories\PreviewFactory;

trait ContentPreviewEditorTrait
{
    use HasBuilderPreview;
    use HasPreviewModal;

    protected function getBuilderPreviewView(string $builderName): ?string
    {
        return 'handle by previewFactory';
    }

    public static function renderBuilderPreview(string $view, array $data): string
    {
        $extraData = Arr::except($data, [
            'documentType',
            'template',
            'recordData',
            'propertyData',
        ]);

        return PreviewFactory::create()->renderContentPreview(
            documentType: $data['documentType'] ?? null,
            template: $data['template'] ?? null,
            content: $data['recordData'] ?? [],
            propertyData: $data['propertyData'] ?? [],
            locale: $data['locale'] ?? null,
            data: $extraData,
        );
    }

    public static function getBuilderEditorSchema(string $builderName): Component | array
    {
        $resource = static::getResource();

        return $resource::getPreviewBuilderEditorSchema($builderName);
    }

    public function mutateInitialBuilderEditorData(string $builderName, array $editorData): array
    {
        $editorData['recordData'] = Arr::except($this->data, [
            'propertyData',
        ]);
        $editorData['propertyData'] = $this->data['propertyData'] ?? [];

        if ($this instanceof CreateRecord) {
            $editorData['editorOperation'] = 'create';
            $editorData['recordData']['children'] = [];

        } else {
            $editorData['editorOperation'] = 'edit';

            $content = $this->getRecord();
            $editorData['recordData']['children'] = $content->children()->pluck($content->getKeyName())->toArray();
        }

        $editorData['documentType'] = ($dt = $this->getDocumentType()) && $dt instanceof Model ? $dt->getKey() : ($dt ?? null); // primary key or model record
        $editorData['template'] = $this->data['template_id'] ?? null;

        return $editorData;
    }

    public static function mutateBuilderPreviewData(string $builderName, array $editorData, array $previewData): array
    {
        return array_merge($previewData, $editorData);
    }
}
