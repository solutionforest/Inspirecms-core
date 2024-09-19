<?php

namespace SolutionForest\InspireCms\Base;

use Illuminate\Support\Str;
use SolutionForest\InspireCms\Facades\PermissionManifest;

class BasePolicy
{
    protected static function guessPermissionName(string $ability, string $model): string
    {
        $ability = Str::snake($ability);
        
        return PermissionManifest::getPermissionNameForModel($ability, $model);
    }
}
