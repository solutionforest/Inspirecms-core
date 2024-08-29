<?php

use SolutionForest\InspireCms\InspireCmsManager;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;

if (! function_exists('inspirecms')) {
    function inspirecms(): InspireCmsManager
    {
        return app(InspireCmsManager::class);
    }
}

if (! function_exists('is_inspirecms_user')) {
    function is_inspirecms_user($user): bool
    {
        $traits = class_uses_recursive($user);

        return in_array(CmsUserTrait::class, $traits);
    }
}
