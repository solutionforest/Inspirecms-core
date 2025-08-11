<?php

namespace SolutionForest\InspireCms\Livewire;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Component;
use SolutionForest\InspireCms\Filament\Resources\ExportResource;
use SolutionForest\InspireCms\Filament\Resources\ImportResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListImportNExport extends Component implements HasActions, HasForms, HasInfolists, HasTable
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

    public function getDefaultActionAuthorizationResponse(Action $action): ?Response
    {
        return match (true) {
            $action instanceof CreateAction => static::getResource()::getCreateAuthorizationResponse(),
            $action instanceof DeleteAction => static::getResource()::getDeleteAuthorizationResponse($action->getRecord()),
            $action instanceof EditAction => static::getResource()::getEditAuthorizationResponse($action->getRecord()),
            $action instanceof ForceDeleteAction => static::getResource()::getForceDeleteAuthorizationResponse($action->getRecord()),
            $action instanceof ReplicateAction => static::getResource()::getReplicateAuthorizationResponse($action->getRecord()),
            $action instanceof RestoreAction => static::getResource()::getRestoreAuthorizationResponse($action->getRecord()),
            $action instanceof ViewAction => static::getResource()::getViewAuthorizationResponse($action->getRecord()),
            $action instanceof DeleteBulkAction => static::getResource()::getDeleteAnyAuthorizationResponse(),
            $action instanceof ForceDeleteBulkAction => static::getResource()::getForceDeleteAnyAuthorizationResponse(),
            $action instanceof RestoreBulkAction => static::getResource()::getRestoreAnyAuthorizationResponse(),
            default => null,
        };
    }

    public function getDefaultActionIndividualRecordAuthorizationResponseResolver(Action $action): ?Closure
    {
        return match (true) {
            $action instanceof DeleteBulkAction => fn (Model $record): Response => static::getResource()::getDeleteAuthorizationResponse($record),
            $action instanceof ForceDeleteBulkAction => fn (Model $record): Response => static::getResource()::getForceDeleteAuthorizationResponse($record),
            $action instanceof RestoreBulkAction => fn (Model $record): Response => static::getResource()::getRestoreAuthorizationResponse($record),
            default => null,
        };
    }

    public function render()
    {
        return view('inspirecms::livewire.list-import-n-export');
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
}
