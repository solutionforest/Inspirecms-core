<?php

namespace SolutionForest\InspireCms\Base\Manifests;

interface ModelManifestInterface
{
    /**
     * Bind initial models to the container and establish explicit model bindings.
     */
    public function register(): void;

    /**
     * Register the morph map for polymorphic relations.
     */
    public function registerMorphMap(): void;

    /**
     * Registers the policies for the application.
     */
    public function registerPolices(): void;

    /**
     * Register models.
     *
     * @param  string  $interfaceClass  The interface class to register.
     * @param  string  $modelClass  The model class to register.
     */
    public function add(string $interfaceClass, string $modelClass): void;

    /**
     * Replace a model with a different implementation.
     *
     * @param  string  $interfaceClass  The interface class to replace.
     * @param  string  $modelClass  The new model class to use.
     */
    public function replace(string $interfaceClass, string $modelClass): void;

    /**
     * Gets the registered class for the interface.
     *
     * @param  string  $interfaceClass  The interface class to retrieve.
     * @param  string|null  $fallback  Optional fallback class if not found.
     * @return string|null The registered model class or fallback.
     */
    public function get(string $interfaceClass, ?string $fallback = null): ?string;
}
