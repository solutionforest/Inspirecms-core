<?php

namespace SolutionForest\InspireCms\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class AccountLockedException extends Exception
{
    public function __construct($message = 'Account is locked.', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function user($user)
    {
        if (is_string($user)) {
            return new static("Account is locked for user: {$user}");
        } elseif ($user instanceof Model) {
            return new static('Account is locked for user: ' . $user->email ?? $user->name ?? $user->getKey());
        } else {
            return new static;
        }
    }
}
