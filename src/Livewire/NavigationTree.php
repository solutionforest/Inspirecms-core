<?php

namespace SolutionForest\InspireCms\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Navigation;
use Throwable;

class NavigationTree extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $category;

    public int $maxDepth = -1;

    public int $maxVisibleDepth = 20;

    #[Reactive]
    public ?string $activeLocale = null;

    public array $nodes = [];

    protected $listeners = [
        'refreshAllTree' => '$refresh',
    ];

    public function mount($category, $activeLocale = null)
    {
        $this->category = $category;
        $this->activeLocale = $activeLocale ?? collect(InspireCms::getAllAvailableLanguages())->keys()->first() ?? app()->getLocale();
        $this->refreshNodes();
    }

    #[On('refreshAllTree')]
    public function refreshNodes()
    {
        $records = $this->getTreeQuery()->get()->toTree();
        $this->nodes = $this->mutateBeforeFill($records);
    }

    public function resetTree()
    {
        $this->refreshNodes();
    }

    public function editTreeNode($id)
    {
        $url = FilamentResourceHelper::attemptToGetUrl($this->getResource(), 'edit', [
            'record' => $id,
            'category' => $this->category,
            'activeLocale' => $this->activeLocale,
        ], false);

        if (blank($url)) {
            Notification::make()
                ->title('Edit Failed')
                ->body('Unable to edit this navigation item')
                ->danger()
                ->send();
        }

        return redirect()->to($url);
    }

    public function viewTreeNode($id)
    {
        $url = FilamentResourceHelper::attemptToGetUrl($this->getResource(), 'view', [
            'record' => $id,
            'category' => $this->category,
            'activeLocale' => $this->activeLocale,
        ], false);

        if (blank($url)) {
            Notification::make()
                ->title('View Failed')
                ->body('Unable to view this navigation item')
                ->danger()
                ->send();
        }

        return redirect()->to($url);
    }

    public function deleteTreeNode($id)
    {
        try {
            $record = $this->getModel()::findOrFail($id);
            $record->delete();
            Notification::make()
                ->title('Deleted')
                ->body('Navigation item deleted successfully.')
                ->success()
                ->send();
        } catch (Throwable $th) {
            Notification::make()
                ->title('Delete Failed')
                ->body('Navigation item not found.')
                ->danger()
                ->send();
        } finally {
            $this->dispatch('refreshAllTree');
        }
    }

    protected function getTreeNodeActions(): array
    {
        return [
            Action::make('view')
                ->icon('heroicon-m-eye')
                ->iconButton()
                ->color('gray')
                ->label('View')
                ->size('xs')
                ->visible(fn () => $this->authorizeAction('view')),
            Action::make('edit')
                ->icon('heroicon-m-pencil')
                ->iconButton()
                ->color('primary')
                ->label('Edit')
                ->size('xs')
                ->visible(fn () => $this->authorizeAction('update')),
            Action::make('delete')
                ->icon('heroicon-m-trash')
                ->iconButton()
                ->color('danger')
                ->label('Delete')
                ->size('xs')
                ->model($this->getModel())
                ->requiresConfirmation()
                ->modalHeading('Delete Navigation Item')
                ->modalDescription('Are you sure you want to delete this navigation item?')
                ->modalAlignment(Alignment::Center),
        ];
    }

    public function getAvailableActions(): array
    {
        return collect($this->getTreeNodeActions())
            ->where(fn (Action $action) => $action->isVisible())
            ->all();
    }

    public function save()
    {
        $data = $this->mutateBeforeSave($this->nodes);
        $this->getTreeQuery()->rebuildTree($data);

        Notification::make()
            ->title('Tree Updated')
            ->body('The navigation tree has been updated successfully.')
            ->success()
            ->send();
        $this->refreshNodes();
    }

    protected function getTreeQuery(): Builder
    {
        return $this->getModel()::scoped(['category' => $this->category])
            ->withDepth()
            ->with([
                'content' => fn ($q) => $q->withTrashed(),
                'children',
            ])
            ->defaultOrder();
    }

    protected function mutateBeforeFill($models): array
    {
        return collect($models)
            ->map(fn (Navigation | Model $model) => [
                'id' => $model->id,
                'name' => str($model->hasTranslation('title', $this->activeLocale) ? $model->getTranslation('title', $this->activeLocale) : $model->title)
                    ->when(! $model->isVisibility(), fn ($str) => str($str)->append(' (Hidden)'))
                    ->toString(),
                'description' => ($url = $model->getUrl($this->activeLocale)) && filled($url) ? $url : null,
                'children' => $this->mutateBeforeFill($model->children),
            ])
            ->toArray();
    }

    protected function mutateBeforeSave($nodes): array
    {
        return collect($nodes)->map(fn ($item) => [
            'id' => $item['id'],
            'title' => $item['name'],
            'children' => $this->mutateBeforeSave($item['children'] ?? []),
        ])->toArray();
    }

    protected function authorizeAction(string $action): bool
    {
        $result = PermissionManifest::authorizeModel($action, $this->getModel(), true);

        if ($result !== null) {
            return $result;
        }

        return false;
    }

    protected function getModel(): string
    {
        return InspireCmsConfig::getNavigationModelClass();
    }

    /**
     * @return class-string<resource>
     */
    protected function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation');
    }

    public function render()
    {
        return view('inspirecms::livewire.navigation-tree', [
            'maxDepth' => $this->maxDepth,
            'maxVisibleDepth' => $this->maxVisibleDepth,
        ]);
    }
}
