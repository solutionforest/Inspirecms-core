<?php

namespace SolutionForest\InspireCms\Base\Enums;

enum UserActivity: string
{
    case Login = 'login';
    case Logout = 'logout';
    case FailedLogin = 'failed_login';
    case PasswordReset = 'password_reset';
    case LockoutReset = 'lockout_reset';
}
