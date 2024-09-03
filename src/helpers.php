<?php

use SolutionForest\InspireCms\Base\Manifests\ContentStatusManifestInterface;
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

if (! function_exists('inspirecms_content_statuses')) {
    function inspirecms_content_statuses(): ContentStatusManifestInterface
    {
        return app(ContentStatusManifestInterface::class);
    }
}
