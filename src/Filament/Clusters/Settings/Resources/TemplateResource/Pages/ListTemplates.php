<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Livewire\Attributes\On;
use Riodwanto\FilamentAceEditor\AceEditor;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource;
use SolutionForest\InspireCms\Support\TreeNodes\Concerns\InteractsWithFileExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasFileExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer;

class ListTemplates extends BaseListPage implements HasFileExplorer
{
    use InteractsWithFileExplorer;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.pages.list-templates';

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.template', TemplateResource::class);
    }

    public function fileExplorer(FileExplorer $fileExplorer): FileExplorer
    {
        return $fileExplorer->directory(resource_path('views'));
    }

    public function getActions(): array
    {
        return [
            Action::make('openTemplateForm')
                ->form([
                    TextInput::make('full_path')
                        ->hiddenLabel()
                        ->disabled()
                        ->dehydrated(),
                    AceEditor::make('content')
                        ->mode('php')
                        ->theme('github')
                        ->darkTheme('dracula'),
                ])
                ->slideOver()
                ->modalWidth('7xl')
                ->modalHeading(function (array $arguments) {

                    $fullPath = $this->getSelectedFileItemPath() ?? $arguments['path'] ?? null;

                    if (blank($fullPath)) {
                        return static::getTitle();
                    }

                    return basename($fullPath);
                })
                ->fillForm(function (array $arguments, Action $action) {

                    $fullPath = $this->getSelectedFileItemPath() ?? $arguments['path'] ?? null;

                    if (blank($fullPath)) {
                        $action->halt();

                        return [];
                    }

                    $content = $this->getFileContent($fullPath);

                    return [
                        'full_path' => $fullPath,
                        'content' => $content,
                    ];
                })
                ->disabledForm(! $this->canEditView())
                ->modalSubmitAction(function ($action) {
                    if (! $this->canEditView()) {
                        return false;
                    }

                    return $action;
                })
                ->extraAttributes(['class' => 'hidden']) // keep it action but hidden on frontend
                ->successNotificationTitle(__('inspirecms::notification.saved.title'))
                ->modalSubmitActionLabel(__('inspirecms::actions.save.label'))
                ->action(function (array $data, Action $action) {

                    file_put_contents($data['full_path'], $data['content']);

                    $action->success();
                }),
        ];
    }

    #[On('selectFileExplorerItem')]
    public function fileExplorerItemSelected($path)
    {
        $this->mountAction('openTemplateForm', ['path' => $path]);
    }

    protected function canEditView(): bool
    {
        return static::getResource()::can('updateView');
    }
}
