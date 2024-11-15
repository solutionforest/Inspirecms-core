<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use Illuminate\Support\Arr;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Concerns\EditorBasicTrait;

#[ConfigName('markdownEditor', 'Markdown Editor', 'Rich', 'heroicon-o-document-text')]
#[FormComponent(Forms\Components\MarkdownEditor::class)]
#[DbType('mysql', 'text')]
#[DbType('sqlite', 'text')]
class MarkdownEditor extends FieldTypeBaseConfig implements FieldTypeConfig
{
    use EditorBasicTrait;

    protected static array $availableToolbarButtons = [
        'attachFiles',
        'blockquote',
        'bold',
        'bulletList',
        'codeBlock',
        'heading',
        'italic',
        'link',
        'orderedList',
        'redo',
        'strike',
        'table',
        'undo',
    ];

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    static::getEditorBasicTraitComponent('toolbarButtons'),
                ]),
            Forms\Components\Section::make('File Attachments')
                ->schema([
                    static::getEditorBasicTraitComponent('fileAttachmentsDisk'),
                    static::getEditorBasicTraitComponent('fileAttachmentsDirectory'),
                    static::getEditorBasicTraitComponent('fileAttachmentsVisibility'),
                ]),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
    {
        ray($this);
        if ($component instanceof Forms\Components\MarkdownEditor) {
            $component->toolbarButtons($this->toolbarButtons);
            if (filled($this->fileAttachmentsDisk)) {
                $component->fileAttachmentsDisk($this->fileAttachmentsDisk);
            }
            if (filled($this->fileAttachmentsDirectory)) {
                $component->fileAttachmentsDirectory($this->fileAttachmentsDisk);
            }
            if (filled($this->fileAttachmentsVisibility)) {
                $component->fileAttachmentsVisibility($this->fileAttachmentsDisk);
            }
        }
    }
}
