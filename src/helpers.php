<?php

use SolutionForest\InspireCms\Base\Assets\InspireCmsAssetManagerInterface;
use SolutionForest\InspireCms\Base\Manifests\ContentStatusManifestInterface;
use SolutionForest\InspireCms\Base\Manifests\LocaleManifestInterface;
use SolutionForest\InspireCms\Base\Manifests\PermissionManifestInterface;
use SolutionForest\InspireCms\InspireCmsManager;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;

if (! function_exists('inspirecms')) {
    function inspirecms(): InspireCmsManager
    {
        return app(InspireCmsManager::class);
    }
}

if (! function_exists('inspirecms_asset')) {
    function inspirecms_asset(): InspireCmsAssetManagerInterface
    {
        return app(InspireCmsAssetManagerInterface::class);
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

if (! function_exists('inspirecms_permissions')) {
    function inspirecms_permissions(): PermissionManifestInterface
    {
        return app(PermissionManifestInterface::class);
    }
}

if (! function_exists('inspirecms_locales')) {
    function inspirecms_locales(): LocaleManifestInterface
    {
        return app(LocaleManifestInterface::class);
    }
}
