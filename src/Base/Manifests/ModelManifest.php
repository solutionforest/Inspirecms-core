<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Models;

class ModelManifest implements ModelManifestInterface
{
    /**
     * The collection of models to register to this manifest.
     */
    protected array $models = [];

    /** {@inheritDoc} */
    public function register(): void
    {
        $modelClasses = static::getDefaultModels();

        foreach ($modelClasses as $modelClass) {
            $interfaceClass = $this->guessContractClass($modelClass);
            $this->models[$interfaceClass] = $modelClass;
            $this->bindModel($interfaceClass, $modelClass);
        }
    }

    /** {@inheritDoc} */
    public function registerMorphMap(): void
    {
        $modelClasses = collect(static::getDefaultModels())->mapWithKeys(
            fn ($class) => [
                $this->getMorphMapKey($class) => $class,
            ]
        );

        Relation::morphMap($modelClasses->toArray());
    }

    /** {@inheritDoc} */
    public function registerPolices(): void
    {
        $modelClasses = static::getDefaultModels();

        foreach ($modelClasses as $modelClass) {
            $interfaceClass = $this->guessContractClass($modelClass);

            $policyClass = $this->guessPolicyClass($interfaceClass);

            if (! class_exists($policyClass)) {
                continue;
            }

            Gate::policy($modelClass, $policyClass);
        }
    }

    /** {@inheritDoc} */
    public function add(string $interfaceClass, string $modelClass): void
    {
        $this->validateClassIsEloquentModel($modelClass);

        $this->models[$interfaceClass] = $modelClass;

        $this->bindModel($interfaceClass, $modelClass);
    }

    /** {@inheritDoc} */
    public function replace(string $interfaceClass, string $modelClass): void
    {
        $this->add($interfaceClass, $modelClass);
    }

    /** {@inheritDoc} */
    public function get(string $interfaceClass, ?string $fallback = null): ?string
    {
        return $this->models[$interfaceClass] ?? $fallback;
    }

    /**
     * Get the default models for registration.
     *
     * @return array The array of default model classes.
     */
    protected static function getDefaultModels(): array
    {
        return config('inspirecms.models.fqcn', []);
    }

    //region Helper methods
    /**
     * Bind a model to the interface in the container.
     *
     * @param  string  $interfaceClass  The interface class to bind.
     * @param  string  $modelClass  The model class to bind.
     */
    protected function bindModel(string $interfaceClass, string $modelClass): void
    {
        app()->bind($interfaceClass, $modelClass);
    }

    /**
     * Guess the contract class for a given model class.
     *
     * @param  string  $modelClass  The model class to guess the contract for.
     * @return string The guessed contract class name.
     */
    protected function guessContractClass(string $modelClass): string
    {
        $class = new \ReflectionClass($modelClass);

        $shortName = $class->getShortName();
        $namespace = str($class->getNamespaceName());

        if (str($namespace)->startsWith('SolutionForest\\InspireCms\\Models')) {
            $namespace = 'SolutionForest\\InspireCms\\Models';
        }

        return "{$namespace}\\Contracts\\$shortName";
    }

    /**
     * Guess the model class for a given contract.
     *
     * @param  string  $modelContract  The model contract to guess the class for.
     * @return string The guessed model class name.
     */
    protected function guessModelClass(string $modelContract): string
    {
        if (
            ! class_exists($modelContract) &&
            $morphedClass = Relation::morphMap()[$modelContract] ?? null
        ) {
            return $morphedClass;
        }

        $shortName = (new \ReflectionClass($modelContract))->getShortName();

        return 'SolutionForest\\InspireCms\\Models\\' . $shortName;
    }

    /**
     * Guess the policy class for a given contract.
     *
     * @param  string  $modelContract  The model contract to guess the class for.
     * @return string The guessed model class name.
     */
    protected function guessPolicyClass(string $modelContract): string
    {
        $shortName = (new \ReflectionClass($modelContract))->getShortName();

        return 'SolutionForest\\InspireCms\\Policies\\' . $shortName . 'Policy';
    }

    /**
     * Get the morph map key for a given class name.
     *
     * This method generates a unique morph map key by combining a prefix (if defined in the configuration)
     * with the snake_case version of the class name's basename.
     *
     * @param  string  $className  The class name to generate the morph map key for.
     * @return string The generated morph map key.
     */
    protected function getMorphMapKey(string $className): string
    {
        $prefix = config('inspirecms.models.morph_map_prefix', null);

        $key = Str::snake(class_basename($className));

        return "{$prefix}{$key}";
    }

    /**
     * Validate that a class is an Eloquent model.
     *
     * @param  string  $class  The class to validate.
     *
     * @throws \InvalidArgumentException If the class is not a subclass of Model.
     */
    private function validateClassIsEloquentModel(string $class): void
    {
        if (! is_subclass_of($class, Model::class)) {
            throw new \InvalidArgumentException(sprintf('Given [%s] is not a subclass of [%s].', $class, Model::class));
        }
    }
    //endregion Helper methods
}
