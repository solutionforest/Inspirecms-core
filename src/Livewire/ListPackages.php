<?php

namespace SolutionForest\InspireCms\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Livewire\Component;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ExportResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\ImportResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListPackages extends Component implements HasActions, HasForms, HasInfolists, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithInfolists;
    use InteractsWithTable;

    public string $type;

    public ?string $title = null;

    public function table(Table $table): Table
    {
        $resource = $this->getResource();

        /**
         * @var Table
         */
        $table = $resource::table($table);

        return $table
            ->heading($this->title ?? Str::title($this->type))
            ->query($resource::getEloquentQuery());
    }

    public function render()
    {
        return view('inspirecms::livewire.list-packages');
    }

    /**
     * @return class-string<resource>
     */
    protected function getResource(): string
    {
        return match ($this->type) {
            'export' => InspireCmsConfig::getFilamentResource('export', ExportResource::class),
            default => InspireCmsConfig::getFilamentResource('import', ImportResource::class),
        };
    }

    protected function getModel(): string
    {
        return $this->getResource()::getModel();
    }

    protected function getModelLabel(): string
    {
        return $this->getResource()::getModelLabel();
    }

    protected function configureCreateAction(CreateAction | Tables\Actions\CreateAction $action): void
    {
        $resource = $this->getResource();

        $action
            ->authorize($resource::canCreate())
            ->model($this->getModel())
            ->modelLabel($this->getModelLabel() ?? $this->getResource()::getModelLabel())
            ->form(fn (Form $form): Form => $resource::form($form->columns(2)));

        if (($action instanceof CreateAction) && $this->getResource()::isScopedToTenant()) {
            $action->relationship(($tenant = Filament::getTenant()) ? fn (): Relation => $this->getResource()::getTenantRelationship($tenant) : null);
        }
    }

    protected function configureTableAction(Tables\Actions\Action $action): void
    {
        match (true) {
            $action instanceof Tables\Actions\CreateAction => $this->configureCreateAction($action),
            $action instanceof Tables\Actions\DeleteAction => $this->configureDeleteAction($action),
            $action instanceof Tables\Actions\EditAction => $this->configureEditAction($action),
            $action instanceof Tables\Actions\ForceDeleteAction => $this->configureForceDeleteAction($action),
            $action instanceof Tables\Actions\ReplicateAction => $this->configureReplicateAction($action),
            $action instanceof Tables\Actions\RestoreAction => $this->configureRestoreAction($action),
            $action instanceof Tables\Actions\ViewAction => $this->configureViewAction($action),
            default => null,
        };
    }

    protected function configureDeleteAction(Tables\Actions\DeleteAction $action): void
    {
        $action
            ->authorize(fn (Model $record): bool => $this->getResource()::canDelete($record));
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        $resource = $this->getResource();

        $action
            ->authorize(fn (Model $record): bool => $resource::canEdit($record))
            ->form(fn (Form $form): Form => $resource::form($form->columns(2)));
    }

    protected function configureForceDeleteAction(Tables\Actions\ForceDeleteAction $action): void
    {
        $action
            ->authorize(fn (Model $record): bool => $this->getResource()::canForceDelete($record));
    }

    protected function configureReplicateAction(Tables\Actions\ReplicateAction $action): void
    {
        $action
            ->authorize(fn (Model $record): bool => $this->getResource()::canReplicate($record));
    }

    protected function configureRestoreAction(Tables\Actions\RestoreAction $action): void
    {
        $action
            ->authorize(fn (Model $record): bool => $this->getResource()::canRestore($record));
    }

    protected function configureViewAction(Tables\Actions\ViewAction $action): void
    {
        $resource = $this->getResource();

        $action
            ->authorize(fn (Model $record): bool => $resource::canView($record))
            ->infolist(fn (Infolist $infolist): Infolist => $resource::infolist($infolist->columns(2)))
            ->form(fn (Form $form): Form => $resource::form($form->columns(2)));
    }

    protected function configureTableBulkAction(BulkAction $action): void
    {
        match (true) {
            $action instanceof Tables\Actions\DeleteBulkAction => $this->configureDeleteBulkAction($action),
            $action instanceof Tables\Actions\ForceDeleteBulkAction => $this->configureForceDeleteBulkAction($action),
            $action instanceof Tables\Actions\RestoreBulkAction => $this->configureRestoreBulkAction($action),
            default => null,
        };
    }

    protected function configureDeleteBulkAction(Tables\Actions\DeleteBulkAction $action): void
    {
        $action
            ->authorize($this->getResource()::canDeleteAny());
    }

    protected function configureForceDeleteBulkAction(Tables\Actions\ForceDeleteBulkAction $action): void
    {
        $action
            ->authorize($this->getResource()::canForceDeleteAny());
    }

    protected function configureRestoreBulkAction(Tables\Actions\RestoreBulkAction $action): void
    {
        $action
            ->authorize($this->getResource()::canRestoreAny());
    }
}
