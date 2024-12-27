<?php

use SolutionForest\InspireCms\Base\Manifests\ContentStatusManifestInterface;
use SolutionForest\InspireCms\Base\Manifests\LocaleManifestInterface;
use SolutionForest\InspireCms\Base\Manifests\PermissionManifestInterface;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;

if (! function_exists('inspirecms')) {
    /**
     * @return \SolutionForest\InspireCms\InspireCmsManager
     */
    function inspirecms()
    {
        return app(\SolutionForest\InspireCms\InspireCmsManager::class);
    }
}

if (! function_exists('inspirecms_templates')) {
    /**
     * @return \SolutionForest\InspireCms\Base\TemplateManager
     */
    function inspirecms_templates()
    {
        return app(\SolutionForest\InspireCms\Base\TemplateManager::class);
    }
}

if (! function_exists('inspirecms_asset')) {
    /**
     * @return \SolutionForest\InspireCms\Services\AssetServiceInterface
     */
    function inspirecms_asset()
    {
        return app(\SolutionForest\InspireCms\Services\AssetServiceInterface::class);
    }
}

if (! function_exists('inspirecms_content')) {
    /**
     * @return \SolutionForest\InspireCms\Services\ContentServiceInterface
     */
    function inspirecms_content()
    {
        return app(\SolutionForest\InspireCms\Services\ContentServiceInterface::class);
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
    /**
     * @return ContentStatusManifestInterface
     */
    function inspirecms_content_statuses()
    {
        return app(ContentStatusManifestInterface::class);
    }
}

if (! function_exists('inspirecms_permissions')) {
    /**
     * @return PermissionManifestInterface
     */
    function inspirecms_permissions()
    {
        return app(PermissionManifestInterface::class);
    }
}

if (! function_exists('inspirecms_locales')) {
    /**
     * @return LocaleManifestInterface
     */
    function inspirecms_locales()
    {
        return app(LocaleManifestInterface::class);
    }
}
