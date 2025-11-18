<?php

namespace SolutionForest\InspireCms\Helpers;

use Throwable;

class ThrowableHelper
{
    /**
     * Returns the stack trace of a Throwable as a string, limited to a specified number of entries.
     *
     * @param  Throwable  $e  The throwable instance to get the stack trace from.
     * @param  int  $limit  The maximum number of stack trace entries to include in the string.
     * @return string The stack trace as a string.
     */
    public static function getTraceAsString(Throwable $e, int $limit): string
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
                    if (is_string($argument)) {
                        return "'" . $argument . "'";
                    }
                    if (is_object($argument)) {
                        return 'object(' . get_class($argument) . ')';
                    }
                    if (is_array($argument)) {
                        return 'array(' . implode(', ', array_map(function ($v) {
                            return is_scalar($v) ? var_export($v, true) : (is_object($v) ? 'object('.get_class($v).')' : gettype($v));
                        }, array_slice($argument, 0, 3))) . (count($argument) > 3 ? ', ...' : '') . ')';
                    }

                    return var_export($argument, true);
                }, $traceItem['args'] ?? []))
            );

            if ($index === $limit) {
                break;
            }
        }

        return $traceAsString;
    }
}
