<?php

namespace SolutionForest\InspireCms\Base\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

trait CreateContentPageTrait
{
    #[Url]
    public $parent = '';

    #[Locked]
    public ?Model $parentRecord = null;

    public function mountCreateContentPageTrait()
    {
        if ($this->parent) {
            $this->parentRecord = $this->resolveParentRecord($this->parent);
        }
    }

    public function getParentRecord(): ?Model
    {
        return $this->parentRecord;
    }

    protected function resolveParentRecord(int | string $key): ?Model
    {
        $record = static::getResource()::resolveRecordRouteBinding($key);

        if ($record === null) {
            $model = new ($this->getModel());
            if (method_exists($model, 'getRootLevelParentId') && $model->getRootLevelParentId() == $key) {
                return null;
            }

            throw (new ModelNotFoundException)->setModel($this->getModel(), [$key]);
        }

        return $record;
    }

    public function getParent(): string | int | Model | null
    {
        return $this->parentRecord ?? $this->parent;
    }

    public function getParentKey(): string | int | null
    {
        return $this->parentRecord?->getKey() ?? $this->parent;
    }
}
