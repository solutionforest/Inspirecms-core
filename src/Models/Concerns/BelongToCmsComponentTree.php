<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

/**
 * This trait provides functionality for models to belong to a CmsComponentTree,
 * allowing them to be organized in a hierarchical structure.
 *
 * It manages the relationship with the CmsComponentTree model and provides methods
 * for creating, updating, and managing the hierarchical structure.
 */
trait BelongToCmsComponentTree
{
    protected bool $updateTreeOrder = false;

    public static function bootBelongToCmsComponentTree()
    {
        static::created(function ($model) {
            $model->createOrUpdateNode();
        });

        static::saved(function ($model) {
            $model->createOrUpdateNode();
        });
    }

    public function componentTree(): MorphOne
    {
        return $this->morphOne(InspireCmsConfig::getComponentTreeModelClass(), 'nestable');
    }

    protected function createOrUpdateNode()
    {
        $nodeData = $this->getTreeData();

        if ($this->componentTree) {
            $this->componentTree->update($nodeData);
        } else {
            $this->componentTree()->create($nodeData);
        }

        $this->load('componentTree');

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

            $componentTreeClass = InspireCmsConfig::getComponentTreeModelClass();

            $parentId = $this->getParentId() ?? $this->fallbackParentId();

            $maxOrder = $componentTreeClass::query()
                ->where('parent_id', $parentId)
                ->when($this->componentTree, fn ($q) => $q->where('id', '!=', $this->componentTree->id))
                ->max('order');

            return $maxOrder !== null ? $maxOrder + 1 : $this->fallbackSort();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function updateSiblingsSort()
    {
        // Check `componentTree` relationship is loaded
        if (! $this->relationLoaded('componentTree')) {
            $this->load('componentTree');
        }

        if (is_null($this->componentTree)) {
            return;
        }

        try {

            $componentTreeClass = InspireCmsConfig::getComponentTreeModelClass();

            $parentId = $this->getParentId() ?? $this->fallbackParentId();

            $siblings = $componentTreeClass::query()
                ->where('parent_id', $parentId)
                ->when($this->componentTree, fn ($q) => $q->where('id', '!=', $this->componentTree->id ?? null))
                ->orderBy('order')
                ->get();

        } catch (\Exception $e) {
            
            // Throw exception that have problem in getSortQuery
            throw new \Exception('Have error on \'' . __METHOD__ . '\'. Please check you table columns or the method \'getParentId\'.', $e->getCode(), $e);
        }

        foreach ($siblings as $index => $sibling) {
            if ($sibling->order >= $this->componentTree->order && $sibling->id != $this->componentTree->id) {
                $sibling->update(['order' => $index + $this->componentTree->order + 1]);
            } else {
                $sibling->update(['order' => $index]);
            }
        }
    }

    protected function fallbackParentId()
    {
        return -1;
    }

    protected function fallbackSort()
    {
        return 1;
    }
}