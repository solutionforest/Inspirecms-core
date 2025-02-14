<?php

namespace SolutionForest\InspireCms\Exceptions;

use InvalidArgumentException;

class UnauthorizedOwnerException extends InvalidArgumentException
{
    public static function forContent(string $contentId)
    {
        return new static("You are not authorized to manage this $contentId.");
    }
}
