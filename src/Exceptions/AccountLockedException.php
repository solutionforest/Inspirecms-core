<?php

namespace SolutionForest\InspireCms\Exceptions;

use Illuminate\Database\Eloquent\Model;

class AccountLockedException extends \Exception
{
    public function __construct($message = 'Account is locked.', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function user($user)
    {
        if (is_string($user)) {
            return new static("Account is locked for user: {$user}");
        } else if ($user instanceof Model) {
            return new static("Account is locked for user: " . $user->email ?? $user->name ?? $user->getKey());
        } else {
            return new static();
        }
    }
}
