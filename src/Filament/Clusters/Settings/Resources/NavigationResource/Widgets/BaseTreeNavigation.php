<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Widgets;

use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Reactive;
use SolutionForest\FilamentTree\Actions\EditAction;
use SolutionForest\FilamentTree\Actions\ViewAction;
use SolutionForest\FilamentTree\Widgets\Tree as BaseWidget;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationCategory;
use SolutionForest\InspireCms\Facades\PermissionManifest;

abstract class BaseTreeNavigation extends BaseWidget
{
    abstract protected function getNavigationCategory(): NavigationCategory;

    protected static int $maxDepth = 3;

    protected bool $enableTreeTitle = true;

    public string $resource = '';

    #[Reactive]
    public ?string $activeLocale = null;

    public string $translatableContentDriver = '';

    public function mount()
    {
        if (blank($this->resource) || ! is_a($this->resource, Resource::class, true)) {
            throw new \Exception('Resource is required for TreeNavigation widget');
        }

        if (blank($this->translatableContentDriver)) {
            throw new \Exception('TranslatableContentDriver is required for TreeNavigation widget');
        }
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        try {

            $driver = new ($this->translatableContentDriver)($this->activeLocale);

            if (! $driver instanceof \Filament\Support\Contracts\TranslatableContentDriver) {
                throw new \Exception('TranslatableContentDriver must implement Filament\Support\Contracts\TranslatableContentDriver');
            }

            return $driver;

        } catch (\Throwable $th) {

            throw $th;
        }
    }

    public function getModel(): string
    {
        return $this->getResource()::getModel();
    }

    //region Tree Configuration
    protected function getTreeQuery(): Builder
    {
        return $this->getModel()::scoped(['category' => $this->getNavigationCategory()->value])
            ->withDepth();
    }

    protected function getWithRelationQuery(): Builder
    {
        return $this->getTreeQuery()->with([
            'content' => fn ($q) => $q->withTrashed(),
            'children',
        ]);
    }

    protected function getSortedQuery(): Builder
    {
        if (in_array('Kalnoy\Nestedset\NodeTrait', class_uses_recursive($this->getModel()))) {
            return $this->getWithRelationQuery()->defaultOrder();
        }

        return parent::getSortedQuery();
    }

    public function getRootLayerRecords(): \Illuminate\Support\Collection
    {
        if (in_array('Kalnoy\Nestedset\NodeTrait', class_uses_recursive($this->getModel()))) {
            return $this->getRecords()->toTree();
        }

        return parent::getRootLayerRecords();
    }

    public function updateTree(?array $list = null): array
    {
        $model = $this->getModel();
        $reload = false;

        // Using "kalnoy/nestedset" package to handle tree structure
        // "kalnoy/nestedset v6.0.4" for Laravel 11
        // "kalnoy/nestedset v6.0.2" for Laravel 10
        if (in_array('Kalnoy\Nestedset\NodeTrait', class_uses_recursive($model))) {
            $this->getTreeQuery()->rebuildTree($list);
            $reload = true;
        }

        if ($reload) {

            Notification::make()
                ->success()
                ->title(__('filament-actions::edit.single.modal.actions.save.label'))
                ->send();

            // Reload data
            $this->dispatch('refreshTree');
        }

        return ['reload' => $reload];
    }

    public function getTreeRecordTitle(?Model $record = null): string
    {
        if (! $record) {
            return '';
        }

        $translatableContentDriver = $this->makeFilamentTranslatableContentDriver();

        $translatableContentDriver->setRecordLocale($record);

        return $record->title;
    }

    public function getTreeRecordDescription(?Model $record = null): string | HtmlString | null
    {
        $url = $record->getUrl($this->activeLocale);

        if (blank($url)) {
            return null;
        }

        return new HtmlString(<<<Html
            <p class="text-xs truncate max-w-[12rem] md:max-w-[7rem] lg:max-w-full">
                $url
            </p>
        Html);
    }

    public function getTreeRecordIcon(?Model $record = null): ?string
    {
        if (! $record->isVisibility()) {
            return 'heroicon-o-eye-slash';
        }

        return null;
    }
    //endregion Tree Configuration

    //region Helpers
    protected function getResource(): string
    {
        return $this->resource;
    }

    protected function authorizeAction(string $action): bool
    {
        $result = PermissionManifest::authorizeModel($action, $this->getModel(), true);

        if ($result !== null) {
            return $result;
        }

        return false;
    }
    //endregion Helpers

    //region Action Configuration
    protected function hasDeleteAction(): bool
    {
        return $this->authorizeAction('delete');
    }

    protected function hasEditAction(): bool
    {
        return $this->authorizeAction('update');
    }

    protected function hasViewAction(): bool
    {
        return $this->authorizeAction('view');
    }

    protected function configureEditAction(EditAction $action): EditAction
    {
        parent::configureEditAction($action);

        $action->form(fn ($form) => $this->getResource()::form($form));

        $action->slideOver();

        $action->after(function () {
            // refresh other tree widget
            $this->dispatch('refreshAllTree');
        });

        return $action;
    }

    protected function configureViewAction(ViewAction $action): ViewAction
    {
        parent::configureViewAction($action);

        $action->form(fn ($form) => $this->getResource()::form($form));

        $action->slideOver();

        return $action;
    }
    //endregion Action Configuration
}
