<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\Model;
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
        return $fileExplorer->directory(resource_path('views/components'));
    }

    public function getActions(): array
    {
        return [
            Action::make('openTemplateForm')
                ->form(fn (Form $form) => $this->form($form))
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
                    
                    if (!isset($data['full_path']) || !isset($data['content'])) {
                        return;
                    }

                    $this->updateViewContent($data['full_path'], $data['content']);

                    $action->success();
                }),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                TextInput::make('full_path')
                    ->hiddenLabel()
                    ->disabled()
                    ->dehydrated(),
                AceEditor::make('content')
                    ->mode('php')
                    ->theme('github')
                    ->darkTheme('dracula')
                    ->height('56rem'),
            ]);
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

    protected function configureEditAction(EditAction $action): void
    {
        parent::configureEditAction($action);

        $action->mutateRecordDataUsing(function (array $data, Model $record) {
            if (!$record instanceof \SolutionForest\InspireCms\Models\Contracts\Template) {
                return [];
            }
            $fullPath = $record->getFileFullPath();
            $data['full_path'] = $fullPath;
            $data['content'] = $this->getFileContent($fullPath);
            return $data;
        });
        $action->using(function (array $data, EditAction $action) {
            if (!isset($data['full_path']) || !isset($data['content'])) {
                $action->cancel();
                return;
            }

            $this->updateViewContent($data['full_path'], $data['content']);

        });
    }

    protected function updateViewContent($fullPath, $content)
    {
        file_put_contents($fullPath, $content);
    }
}
