<?php

namespace SolutionForest\InspireCms\Base\Interfaces;

use Illuminate\Support\Collection;

interface NestableInterface
{
    /**
     * Get the descendants of the current entity.
     *
     * This method should return a collection of all descendant entities
     * related to the current entity, allowing for hierarchical data
     * retrieval.
     *
     * @return Collection A collection of descendant entities.
     */
    public function descendants(): Collection;

    /**
     * Get the ancestors of the current entity.
     *
     * This method should return a collection of all ancestor entities
     * related to the current entity, allowing for hierarchical data
     * retrieval.
     *
     * @return Collection A collection of ancestor entities.
     */
    public function ancestors(): Collection;

    /**
     * Get the level of the current entity in the hierarchy.
     *
     * This method returns an integer representing the depth of the
     * current entity within its parent-child structure.
     *
     * @return int The level of the current entity.
     */
    public function getLevel(): int;

    /**
     * Determine if the current entity is a root entity.
     *
     * This method returns a boolean indicating whether the current
     * entity has no parent, thus making it a root in the hierarchy.
     *
     * @return bool True if the entity is a root, false otherwise.
     */
    public function isRoot(): bool;

    /**
     * Determine if the current entity is a leaf entity.
     *
     * This method returns a boolean indicating whether the current
     * entity has no children, thus making it a leaf in the hierarchy.
     *
     * @return bool True if the entity is a leaf, false otherwise.
     */
    public function isLeaf(): bool;
}
