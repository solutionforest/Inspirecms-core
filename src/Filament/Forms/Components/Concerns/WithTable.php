<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\Concerns;

use Closure;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Component as ColumnLayoutComponent;
use Illuminate\Database\Eloquent\Model;

trait WithTable
{
    protected array $contentGrid = [];

    protected array $tableColumns = [];

    /**
     * @var array<Column | ColumnLayoutComponent | ColumnGroup>
     */
    protected array $tableColumnsLayout = [];

    protected ?ColumnLayoutComponent $collapsibleTableColumnsLayout = null;

    protected string $tableSortDirection = 'asc';

    protected bool $hasTableColumnsLayout = false;

    protected Closure|array $recordClasses = [];

    public function contentGrid(array $grid): static
    {
        $this->contentGrid = $grid;

        return $this;
    }

    public function tableColumns(array $components): static
    {
        $this->tableColumns = [];
        $this->tableColumnsLayout = [];
        $this->collapsibleTableColumnsLayout = null;
        $this->hasTableColumnsLayout = false;
        $this->pushTableColumns($components);

        return $this;
    }

    public function pushTableColumns(array $components): static
    {
        foreach ($components as $component) {

            if ($component instanceof ColumnLayoutComponent && $component->isCollapsible()) {
                $this->collapsibleTableColumnsLayout = $component;
            } else {
                $this->tableColumnsLayout[] = $component;
            }

            if ($component instanceof ColumnGroup) {
                $this->hasColumnGroups = true;

                $this->tableColumns = [
                    ...$this->tableColumns,
                    ...$component->getColumns(),
                ];

                continue;
            }

            if ($component instanceof ColumnLayoutComponent) {
                $this->hasTableColumnsLayout = true;

                $this->tableColumns = [
                    ...$this->tableColumns,
                    ...$component->getColumns(),
                ];

                continue;
            }

            $this->tableColumns[$component->getName()] = $component;
        }

        return $this;
    }

    public function tableSortDirection(string $direction): static
    {
        $this->tableSortDirection = $direction;

        return $this;
    }

    public function recordClasses(Closure|array $class): static
    {
        $this->recordClasses = $class;

        return $this;
    }

    public function getContentGrid(): array
    {
        return $this->contentGrid;
    }

    public function getTableColumns(): array
    {
        return $this->tableColumns;
    }

    public function getTableColumnsLayout(): array
    {
        return $this->tableColumnsLayout;
    }

    public function getCollapsibleTableColumnsLayout(): ?ColumnLayoutComponent
    {
        return $this->collapsibleTableColumnsLayout;
    }

    public function getVisibleColumns(): array
    {
        return array_filter(
            $this->getTableColumns(),
            fn (Column $column): bool => $column->isVisible() && (! $column->isToggledHidden()),
        );
    }

    public function getSortColumn(): array
    {
        return array_filter(
            $this->getTableColumns(),
            fn (Column $column): bool => $column->isSortable(),
        );
    }

    public function getSortDirection(): string
    {
        return $this->tableSortDirection;
    }

    public function hasTableColumnsLayout(): bool
    {
        return $this->hasTableColumnsLayout || ! empty(array_filter($this->contentGrid));
    }

    public function getRecordClasses(Model $record): array
    {
        return $this->evaluate($this->recordClasses, ['record' => $record]);
    }

    public function getRecordKey(Model $record): string | int
    {
        return $record->getKey();
    }
}
