<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Concerns;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Url;

trait DocumentTypeFormTrait
{
    #[Url('parent')]
    public string | int | null $parent = null;

    public ?Model $persistedParent = null;

    public function mountDocumentTypeFormTrait(): void
    {
        $this->getParent();
    }

    public function getParentKey(): string | int | null
    {
        switch (true) {
            case $this instanceof EditRecord:
            case $this instanceof ViewRecord:
                return $this->getRecord()->getParentId();

            case $this instanceof CreateRecord:
                return $this->parent;

            default:
                return null;
        }
    }

    public function getParent(): ?Model
    {
        switch (true) {
            case $this instanceof EditRecord:
            case $this instanceof ViewRecord:
                $parent = $this->getRecord()->parent;

                break;

            case $this instanceof CreateRecord:
                if (filled($this->parent)) {
                    $parent = $this->resolveParent($this->parent);
                }

                break;
        }

        return $this->persistedParent = $parent ?? null;
    }

    public function canBeParent($parentKey): bool
    {
        $parent = $this->persistedParent ?? $this->resolveParent($parentKey);

        if ($parent === null) {
            return false;
        }

        return $parent->canBeParent();
    }

    protected function resolveParent($parentKey): ?Model
    {
        if (empty($parentKey)) {
            return null;
        }

        $resource = static::getResource();

        return $resource::resolveRecordRouteBinding($parentKey);
    }
}
