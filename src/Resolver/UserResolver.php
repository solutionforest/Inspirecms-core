<?php

namespace SolutionForest\InspireCms\Resolver;

use Illuminate\Support\Facades\Auth;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class UserResolver
{
    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public static function resolve()
    {
        $guard = InspireCmsConfig::getGuardName();

        try {
            $authenticated = Auth::guard($guard)->check();
        } catch (\Exception $exception) {
            return null;
        }

        if ($authenticated === true) {
            return Auth::guard($guard)->user();
        }

        return null;
    }
}
