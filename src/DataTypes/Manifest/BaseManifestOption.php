<?php

namespace SolutionForest\InspireCms\DataTypes\Manifest;

use Filament\Support\Concerns\EvaluatesClosures;

abstract class BaseManifestOption
{
    use EvaluatesClosures
    {
        resolveDefaultClosureDependencyForEvaluationByName as private resolveBaseDefaultClosureDependencyForEvaluationByName;
        resolveDefaultClosureDependencyForEvaluationByType as private resolveBaseDefaultClosureDependencyForEvaluationByType;
    }

    /** {@inheritDoc} */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'option' => [$this],
            default => $this->resolveBaseDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /** {@inheritDoc} */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        return $this->resolveBaseDefaultClosureDependencyForEvaluationByType($parameterType);
    }
}
