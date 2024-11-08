<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\InspireCmsConfig;

class ContentPicker extends PaginationPicker
{
    protected ?Closure $modifyPaginationOptionsUsing = null;

    protected null | Model | string | int | array | Closure $exceptRecord = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->tableColumns([
            TextColumn::make('id')->label(__('inspirecms::resources/content.id.label')),
            TextColumn::make('title')->label(__('inspirecms::resources/content.title.label')),
            TextColumn::make('slug')->label(__('inspirecms::resources/content.slug.label'))->badge(),
        ]);

        $this->recordTitleUsing(fn ($record) => $record->title);
    }

    public function modifyPaginationOptionsUsing(Closure $callback): static
    {
        $this->modifyPaginationOptionsUsing = $callback;

        return $this;
    }

    public function exceptRecord(Model | string | int | array | Closure $record): static
    {
        $this->exceptRecord = $record;

        return $this;
    }

    protected function getPaginationOptionsQuery(): ?Builder
    {
        $query = $this->evaluate($this->paginationOptions);

        if (! $query) {
            $query = InspireCmsConfig::getContentModelClass()::query();
        }

        if ($this->exceptRecord) {
            $record = $this->evaluate($this->exceptRecord);

            if ($record instanceof Model) {
                $query = $query->whereKeyNot($record->getKey());
            } elseif (is_array($record)) {
                $recordKeys = collect($record)
                    ->map(fn ($record) => $record instanceof Model ? $record->getKey() : $record)
                    ->filter()
                    ->unique()
                    ->all();
                $query = $query->whereKeyNot($recordKeys);
            } elseif (is_string($record) || is_int($record)) {
                $query = $query->whereKeyNot($record);
            }
        }

        if ($this->modifyPaginationOptionsUsing) {
            $query = $this->evaluate($this->modifyPaginationOptionsUsing, [
                'query' => $query,
            ]);
        }

        return $query;
    }
}
