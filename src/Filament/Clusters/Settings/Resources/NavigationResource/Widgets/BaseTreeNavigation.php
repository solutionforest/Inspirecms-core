<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\NavigationResource\Widgets;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
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

    protected function getTreeQuery(): Builder
    {
        return $this->getResource()::getEloquentQuery()->category($this->getNavigationCategory()->value);
    }

    protected function getResource(): string
    {
        return $this->resource;
    }

    public function getTreeRecordTitle(?\Illuminate\Database\Eloquent\Model $record = null): string
    {
        if (! $record) {
            return '';
        }

        $translatableContentDriver = $this->makeFilamentTranslatableContentDriver();

        $translatableContentDriver->setRecordLocale($record);

        return $record->title;
    }

    //region Helpers
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

        return $action;
    }

    protected function configureViewAction(ViewAction $action): ViewAction
    {
        parent::configureViewAction($action);

        $action->form(fn ($form) => $this->getResource()::form($form));

        return $action;
    }
    //endregion Action Configuration
}
