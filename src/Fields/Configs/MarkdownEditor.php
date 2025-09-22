<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Actions\Action;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Converter;
use SolutionForest\InspireCms\Fields\Configs\Concerns\EditorBasicTrait;
use SolutionForest\InspireCms\Fields\Converters\MarkdownConverter;
use SolutionForest\InspireCms\Filament\Forms\Components\MarkdownEditor as FormsMarkdownEditor;

#[ConfigName('markdownEditor', 'Markdown Editor', 'Rich', 'heroicon-o-document-text')]
#[FormComponent(FormsMarkdownEditor::class)]
#[DbType('mysql', 'text')]
#[DbType('sqlite', 'text')]
#[Converter(MarkdownConverter::class)]
class MarkdownEditor extends FieldTypeBaseConfig implements FieldTypeConfig
{
    use EditorBasicTrait;

    private static string $toolbarBtnDocUrl = 'https://filamentphp.com/docs/4.x/forms/markdown-editor#customizing-the-toolbar-buttons';

    public function getFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    static::getEditorBasicTraitComponent('toolbarButtonType'),

                    static::getEditorBasicTraitComponent('toolbarButtons')
                        ->hintAction(
                            Action::make('doc')
                                ->label('Available buttons')
                                ->url(self::$toolbarBtnDocUrl)
                                ->icon(Heroicon::OutlinedBookOpen)
                                ->color('primary')
                                ->openUrlInNewTab(),
                        )
                        ->visible(fn ($get) => $get('toolbarButtonType') === 'buttons'),

                    static::getEditorBasicTraitComponent('toolbarButtonGroups')
                        ->hintAction(
                            Action::make('doc')
                                ->label('Available buttons')
                                ->url(self::$toolbarBtnDocUrl)
                                ->icon(Heroicon::OutlinedBookOpen)
                                ->color('primary')
                                ->openUrlInNewTab(),
                        )
                        ->visible(fn ($get) => $get('toolbarButtonType') === 'buttonGroups'),
                ]),
            Section::make('File Attachments')
                ->schema([
                    static::getEditorBasicTraitComponent('fileAttachmentsDisk'),
                    static::getEditorBasicTraitComponent('fileAttachmentsDirectory'),
                    // static::getEditorBasicTraitComponent('fileAttachmentsVisibility'),
                ]),
        ];
    }

    public function applyConfig(Component $component): void
    {
        if ($component instanceof FormsMarkdownEditor) {

            switch ($this->toolbarButtonType) {
                case 'buttons':
                    $component->toolbarButtons($this->toolbarButtons);

                    break;
                case 'buttonGroups':
                    $component->toolbarButtons(
                        collect($this->toolbarButtonGroups)
                            ->pluck('buttons')
                            ->all()
                    );

                    break;
                default:
                    // default to buttons
                    $component->toolbarButtons($this->toolbarButtons);

                    break;
            }

            if (filled($this->fileAttachmentsDisk)) {
                $component->fileAttachmentsDisk($this->fileAttachmentsDisk);
            }
            if (filled($this->fileAttachmentsDirectory)) {
                $component->fileAttachmentsDirectory($this->fileAttachmentsDirectory);
            }
            // Commented out since:
            //  The visibility of file attachments for markdown content is always `public`, since generating temporary file upload URLs is not supported in static content.
            // if (filled($this->fileAttachmentsVisibility)) {
            //     $component->fileAttachmentsVisibility($this->fileAttachmentsVisibility);
            // }
        }
    }

    public static function getAllAvailableToolbarButtons(): array
    {
        try {
            $field = FormsMarkdownEditor::make('tmp');
            $btns = $field->getDefaultToolbarButtons();
            if (is_array($btns)) {
                return static::formatAsSelectableArray(collect($btns)->flatten()->unique()->values()->toArray());
            }
        } catch (\Exception $e) {
            //
        }

        return [];
    }
}
