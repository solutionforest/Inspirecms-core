<?php

namespace SolutionForest\InspireCms\Livewire;

use Exception;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithoutUrlPagination;

class TableReleatedLivewireComponent extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;
    use WithoutUrlPagination;

    // #[Locked]
    // public bool $isDisabled = false;

    #[Locked]
    public Model $ownerRecord;

    #[Locked]
    public string $relationshipName;

    #[Locked]
    public string $tableConfiguration;

    /**
     * @var array<mixed>
     */
    #[Locked]
    public array $tableArguments = [];

    public function table(Table $table): Table
    {
        $tableConfiguration = base64_decode($this->tableConfiguration);

        if (! class_exists($tableConfiguration)) {
            throw new Exception("Table configuration class [{$tableConfiguration}] does not exist.");
        }

        if (! method_exists($tableConfiguration, 'configure')) {
            throw new Exception("Table configuration class [{$tableConfiguration}] does not have a [configure(Table \$table): Table] method.");
        }

        $tableConfiguration::configure($table);

        $table
            ->relationship(fn (): Relation | Builder => $this->getRelationship())
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateActions([])
            // ->selectable()
            // ->trackDeselectedRecords(false)
            // ->deselectAllRecordsWhenFiltered(false)
            // ->disabledSelection($this->isDisabled)
            ->arguments($this->getTableArguments());

        return $table;
    }

    /**
     * @return array<mixed>
     */
    public function getTableArguments(): array
    {
        return $this->tableArguments;
    }

    public function getOwnerRecord(): Model
    {
        return $this->ownerRecord;
    }

    public function getRelationship(): Relation | Builder
    {
        return $this->getOwnerRecord()->{$this->relationshipName}();
    }

    public function render()
    {
        return '{{ $this->table }}';
    }

    public function getTriggerTableActions(): array
    {
        return [];
    }
}
