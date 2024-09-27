<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait NestableTrait
{
    public static function bootNestableTrait()
    {
        // static::addGlobalScope('appendLevel', function ($builder) {
        //     $builder->addSelect(['level' => static::selectRaw('COUNT(*) - 1')
        //         ->from('model_nestables as ancestors')
        //         ->whereRaw('ancestors.id = model_nestables.id OR ancestors.id = model_nestables.parent_id')
        //         ->whereRaw('ancestors.id = model_nestables.id OR EXISTS (
        //             SELECT 1 FROM model_nestables as descendants
        //             WHERE descendants.id = model_nestables.id
        //             AND ancestors.id = descendants.parent_id
        //         )')
        //         ->groupBy('ancestors.id')
        //     ]);
        // });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->getNestableParentIdColumn());
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->getNestableParentIdColumn());
    }

    public function descendants(): Collection
    {
        return $this->children->flatMap(function ($child) {
            return $child->descendants()->prepend($child);
        })->prepend($this);
    }

    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current !== null) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    public function getLevelAttribute(): int
    {
        return $this->getLevel();
    }

    public function getLevel(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    public function isRoot(): bool
    {
        return $this->{$this->getNestableParentIdColumn()} === $this->getNestableRootValue();
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Get the name of 'parent id' column.
     *
     * @return ?string
     */
    protected function getNestableParentIdColumn()
    {
        return 'parent_id';
    }

    public function getNestableRootValue(): int|string
    {
        return 0;
    }
}
