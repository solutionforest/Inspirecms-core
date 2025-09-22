<?php

namespace SolutionForest\InspireCms\Filament\Schemas\Components\StateCasts;

use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

class ArrayChunkStateCast implements StateCast
{
    public function __construct(
        protected int $chunkSize = 3,
    ) {}

    /**
     * Convert depth 2 array back to depth 1 array (flatten)
     */
    public function get(mixed $state): array
    {
        if (is_null($state) || (is_array($state) && empty($state)) || $state === '') {
            return [];
        }

        if (! is_array($state)) {
            $state = json_decode($state, associative: true);
        }

        // If it's already a flat array, return as is
        if (! $this->isMultiDimensional($state)) {
            return $state;
        }

        // Flatten the array
        return array_reduce(
            $state,
            function (array $carry, $chunk): array {
                if (is_array($chunk)) {
                    return array_merge($carry, $chunk);
                }

                $carry[] = $chunk;

                return $carry;
            },
            initial: [],
        );
    }

    /**
     * Convert depth 1 array to depth 2 array (chunk)
     */
    public function set(mixed $state): array
    {
        if (is_null($state) || (is_array($state) && empty($state)) || $state === '') {
            return [];
        }

        if (! is_array($state)) {
            $state = json_decode($state, associative: true);
        }

        // If it's already multi-dimensional, return as is
        if ($this->isMultiDimensional($state)) {
            return $state;
        }

        // Chunk the flat array
        return array_chunk($state, $this->chunkSize);
    }

    /**
     * Check if array is multi-dimensional
     */
    protected function isMultiDimensional(array $array): bool
    {
        return count($array) !== count($array, COUNT_RECURSIVE);
    }
}
