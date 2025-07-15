<?php

namespace SolutionForest\InspireCms\Helpers;

class DiffHelper
{
    public static function compareArrays(array $from, array $to): array
    {
        $diff = [];
        foreach ($from as $key => $value) {
            if (! array_key_exists($key, $to) || $to[$key] !== $value) {
                $diff[$key] = ['from' => $value, 'to' => $to[$key] ?? null];
            }
        }

        foreach ($to as $key => $value) {
            if (! array_key_exists($key, $from)) {
                $diff[$key] = ['from' => null, 'to' => $value];
            }
        }

        return $diff;
    }
}
