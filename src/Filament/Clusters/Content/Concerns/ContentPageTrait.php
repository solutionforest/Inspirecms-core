<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

trait ContentPageTrait
{
    #[Url]
    public $parent = '';

    #[Locked]
    public ?Model $parentRecord = null;

    public function mountContentPageTrait()
    {
        if ($this->parent) {
            $this->parentRecord = $this->resolveParentRecord($this->parent);
        }
    }

    public function getParentRecord(): ?Model
    {
        return $this->parentRecord;
    }

    protected function resolveParentRecord(int | string $key): Model
    {
        $record = static::getResource()::resolveRecordRouteBinding($key);

        if ($record === null) {
            throw (new ModelNotFoundException)->setModel($this->getModel(), [$key]);
        }

        return $record;
    }
}
