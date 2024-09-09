<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Riodwanto\FilamentAceEditor\AceEditor;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource;
use SolutionForest\InspireCms\Support\TreeNodes\Concerns\InteractsWithFileExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasFileExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer;

class ListTemplates extends ListRecords implements HasFileExplorer
{
    use InteractsWithFileExplorer;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.pages.list-templates';

    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }
    
    public static function getResource(): string
    {
        return config('inspirecms.resources.template', TemplateResource::class);
    }

    public function fileExplorer(FileExplorer $fileExplorer): FileExplorer
    {
        return $fileExplorer
        ->directory(config('inspirecms.template.path'));
    }

    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            'selectedFileItemForm' => $this->makeForm()
                ->columns(1)
                ->statePath('fileExplorerSelectedItemData')
                ->schema([
                    TextInput::make('path')->disabled()->inlineLabel(),
                    Hidden::make('full_path')->dehydratedWhenHidden(true),
                    AceEditor::make('content')
                        ->mode('php')
                        ->theme('github')
                        ->darkTheme('dracula'),
                ]),
        ]);
    }

    public function getSelectedFileItemFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('inspirecms::actions.save.label'))
                ->submit('saveSelectedItem')
                ->keyBindings(['mod+s'])
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('inspirecms::notification.saved.title'));
    }

    public function saveSelectedItem()
    {
        $data = $this->selectedFileItemForm->getState();
        
        file_put_contents($data['full_path'], $data['content']);

        $this->getSavedNotification()?->send();
    }
}
