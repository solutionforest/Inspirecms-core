<?php

namespace SolutionForest\InspireCms\Helpers;

use SolutionForest\InspireCms\InspireCmsConfig;

class AuthHelper
{
    public static function guardName(): string
    {
        return InspireCmsConfig::get('auth.guard.name', 'inspirecms');
    }

    public static function providerName(): string
    {
        return InspireCmsConfig::get('auth.provider.name', 'cms_users');
    }

    public static function maxAttempts(): int
    {
        return intval(InspireCmsConfig::get('auth.failed_login_attempts', 5));
    }

    public static function skipAccountVerification(): bool
    {
        return boolval(InspireCmsConfig::get('auth.skip_account_verification', false));
    }
}
