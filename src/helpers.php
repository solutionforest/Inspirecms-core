<?php

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Manifests\ContentStatusManifestInterface;
use SolutionForest\InspireCms\Base\Manifests\LocaleManifestInterface;
use SolutionForest\InspireCms\Base\Manifests\PermissionManifestInterface;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Models\Concerns\CmsUserTrait;

if (! function_exists('inspirecms')) {
    /**
     * @return \SolutionForest\InspireCms\InspireCms
     */
    function inspirecms()
    {
        return app(\SolutionForest\InspireCms\InspireCms::class);
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
    /**
     * Determine if the given user is an InspireCMS user.
     *
     * This function checks if the provided user either uses the \SolutionForest\InspireCms\Models\Concerns\CmsUserTrait
     * or is an instance of the \SolutionForest\InspireCms\Models\Contracts\User class.
     *
     * @param mixed $user The user to check.
     * @return bool Returns true if the user is an InspireCMS user, otherwise false.
     */
    function is_inspirecms_user($user): bool
    {
        return in_array(CmsUserTrait::class, class_uses($user)) || 
            $user instanceof \SolutionForest\InspireCms\Models\Contracts\User ;
    }
}

if (! function_exists('has_super_admin_role')) {
    /**
     * Determine if the given user has the super admin role.
     *
     * This function checks if the provided user either uses the \Spatie\Permission\Traits\HasRoles trait
     * and has the super admin role.
     *
     * @param mixed $user The user to check.
     * @return bool Returns true if the user has the super admin role, otherwise false.
     */
    function has_super_admin_role($user): bool
    {
        if (is_null($user)) {
            return false;
        }
        if (is_inspirecms_user($user)) {
            return $user->isSuperAdmin();
        }

        $roleName = inspirecms_permissions()->getSuperAdminRoleName();
        $guardName = AuthHelper::guardName();

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($roleName, $guardName);

        } elseif ($user instanceof Model) {
            return $user->relationLoaded('roles') 
                ? $user->roles->contains(fn($role) => $role->name === $roleName && $role->guard_name === $guardName)
                : $user->roles()->where('name', $roleName)->where('guard_name', $guardName)->exists();

        }

        return false;
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
