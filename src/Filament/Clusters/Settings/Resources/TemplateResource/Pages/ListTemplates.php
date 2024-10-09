<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource;
use SolutionForest\InspireCms\Support\TreeNodes\Concerns\InteractsWithFileExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasFileExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer;

class ListTemplates extends BaseListPage implements HasFileExplorer, HasForms
{
    use InteractsWithFileExplorer;
    use InteractsWithForms;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.pages.list-templates';

    public array $fileExplorerSelectedItemData = [];

    public ?string $selectedFileItemContent = '';

    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.template', TemplateResource::class);
    }

    public function fileExplorer(FileExplorer $fileExplorer): FileExplorer
    {
        return $fileExplorer
            ->directory(resource_path('views'))
            ->selectedFileItemFormSchema([
                TextInput::make('path')
                    ->disabled()
                    ->inlineLabel(),
                TextInput::make('full_path')
                    ->hidden()
                    ->dehydratedWhenHidden(),
                TextArea::make('content')
                    ->rows(20)
                    ->helperText('TODO: ace editor have debugs, using textarea for temp solution'),
            ]);
    }

    public function getSelectedFileItemFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('inspirecms::actions.save.label'))
                ->submit('saveSelectedItem')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('inspirecms::notification.saved.title'));
    }

    public function saveSelectedItem(): void
    {
        $data = $this->selectedFileItemForm->getState();

        file_put_contents($data['full_path'], $data['content']);

        $this->getSavedNotification()?->send();
    }
}
