<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

/**
 * This trait provides functionality for models to belong to a NestableTree,
 * allowing them to be organized in a hierarchical structure.
 *
 * It manages the relationship with the NestableTree model and provides methods
 * for creating, updating, and managing the hierarchical structure.
 */
trait BelongToCmsNestableTree
{
    protected bool $updateTreeOrder = false;

    public static function bootBelongToCmsNestableTree()
    {
        static::created(function ($model) {
            $model->createOrUpdateNode();
        });

        static::saved(function ($model) {
            $model->createOrUpdateNode();
        });
    }

    public function nestableTree(): MorphOne
    {
        return $this->morphOne(InspireCmsConfig::getNestableTreeModelClass(), 'nestable');
    }

    protected function createOrUpdateNode()
    {
        $nodeData = $this->getTreeData();

        if ($this->nestableTree) {
            $this->nestableTree->update($nodeData);
        } else {
            $this->nestableTree()->create($nodeData);
        }

        $this->load('nestableTree');

        $this->updateSiblingsSort();
    }

    protected function getTreeData(): array
    {
        $data = [
            'parent_id' => $this->getParentId() ?? $this->fallbackParentId(),
            // Add any other fields that should be stored in the Node model
        ];

        if ($this->updateTreeOrder) {
            $data['order'] = $this->calculateOrder();
        }

        return $data;
    }

    protected function getParentId()
    {
        // Override this method in your model to determine the parent_id
        // For example, you might have a `getParentId()` method in your model
        return method_exists($this, 'getParentId') ? ($this->getParentId() ?? $this->fallbackParentId()) : $this->fallbackParentId();
    }

    protected function calculateOrder(): int
    {
        try {

            $nestableTreeClass = InspireCmsConfig::getNestableTreeModelClass();

            $parentId = $this->getParentId() ?? $this->fallbackParentId();

            $maxOrder = $nestableTreeClass::query()
                ->where('parent_id', $parentId)
                ->when($this->nestableTree, fn ($q) => $q->where('id', '!=', $this->nestableTree->id))
                ->max('order');

            return $maxOrder !== null ? $maxOrder + 1 : $this->fallbackSort();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function updateSiblingsSort()
    {
        // Check `nestableTree` relationship is loaded
        if (! $this->relationLoaded('nestableTree')) {
            $this->load('nestableTree');
        }

        if (is_null($this->nestableTree)) {
            return;
        }

        try {

            $nestableTreeClass = InspireCmsConfig::getNestableTreeModelClass();

            $parentId = $this->getParentId() ?? $this->fallbackParentId();

            $siblings = $nestableTreeClass::query()
                ->where('parent_id', $parentId)
                ->when($this->nestableTree, fn ($q) => $q->where('id', '!=', $this->nestableTree->id ?? null))
                ->orderBy('order')
                ->get();

        } catch (\Exception $e) {

            // Throw exception that have problem in getSortQuery
            throw new \Exception('Have error on \'' . __METHOD__ . '\'. Please check you table columns or the method \'getParentId\'.', $e->getCode(), $e);
        }

        foreach ($siblings as $index => $sibling) {
            if ($sibling->order >= $this->nestableTree->order && $sibling->id != $this->nestableTree->id) {
                $sibling->update(['order' => $index + $this->nestableTree->order + 1]);
            } else {
                $sibling->update(['order' => $index]);
            }
        }
    }

    protected function fallbackParentId()
    {
        return 0;
    }

    protected function fallbackSort()
    {
        return 1;
    }
}
