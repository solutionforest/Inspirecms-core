<?php

namespace SolutionForest\InspireCms\Helpers;

class ThrowableHelper
{
    /**
     * Returns the stack trace of a Throwable as a string, limited to a specified number of entries.
     *
     * @param \Throwable $e The throwable instance to get the stack trace from.
     * @param int $limit The maximum number of stack trace entries to include in the string.
     * @return string The stack trace as a string.
     */
    public static function getTraceAsString(\Throwable $e, int $limit): string
    {
        $trace = $e->getTrace();

        $traceAsString = '';

        foreach ($trace as $index => $traceItem) {
            $traceAsString .= sprintf(
                "#%s %s(%s): %s%s%s(%s)\n",
                $index,
                $traceItem['file'] ?? 'unknown',
                $traceItem['line'] ?? 'unknown',
                $traceItem['class'] ?? '',
                $traceItem['type'] ?? '',
                $traceItem['function'] ?? 'unknown',
                implode(', ', array_map(function ($argument) {
                    return is_string($argument) ? "'$argument'" : $argument;
                }, $traceItem['args'] ?? []))
            );

            if ($index === $limit) {
                break;
            }
        }

        return $traceAsString;
    }
}
