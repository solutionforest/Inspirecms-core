<?php

namespace SolutionForest\InspireCms\Support;

use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Models;
use Spatie\Permission\PermissionRegistrar;

class InspireCmsConfig
{
    public static function getGuardName(): string
    {
        return config('inspirecms.auth.guard', 'inspirecms');
    }

    public static function getContentTableName(): string
    {
        return app(static::getContentModelClass())->getTable();
    }

    public static function getContentModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Content::class, Models\Content::class);

        return self::ensureClassExists($class, 'Content model');
    }

    public static function getComponentFieldGroupTableName(): string
    {
        return app(static::getComponentFieldGroupModelClass())->getTable();
    }

    public static function getComponentFieldGroupModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ComponentFieldGroup::class, Models\Polymorphic\ComponentFieldGroup::class);

        return self::ensureClassExists($class, 'ComponentFieldGroup model');
    }

    public static function getComponentTreeTableName(): string
    {
        return app(static::getComponentTreeModelClass())->getTable();
    }

    public static function getComponentTreeModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ComponentTree::class, Models\Polymorphic\ComponentTree::class);

        return self::ensureClassExists($class, 'ComponentTree model');
    }

    public static function getPropertyDataTableName(): string
    {
        return app(static::getPropertyDataModelClass())->getTable();
    }

    public static function getPropertyDataModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\PropertyData::class, Models\PropertyData::class);

        return self::ensureClassExists($class, 'PropertyData model');
    }

    public static function getContentVersionTableName(): string
    {
        return app(static::getContentVersionModelClass())->getTable();
    }

    public static function getContentVersionModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ContentVersion::class, Models\ContentVersion::class);

        return self::ensureClassExists($class, 'ContentVersion model');
    }

    public static function getDocumentTypeTableName(): string
    {
        return app(static::getDocumentTypeModelClass())->getTable();
    }

    public static function getDocumentTypeModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\DocumentType::class, Models\DocumentType::class);

        return self::ensureClassExists($class, 'DocumentType model');
    }

    public static function getFieldGroupTableName(): string
    {
        return app(static::getFieldGroupModelClass())->getTable();
    }

    public static function getFieldGroupModelClass(): string
    {
        return \SolutionForest\FilamentFieldGroup\Supports\FieldGroupConfig::getFieldGroupModelClass();
    }

    public static function getFieldTableName(): string
    {
        return app(static::getFieldModelClass())->getTable();
    }

    public static function getFieldModelClass(): string
    {
        return \SolutionForest\FilamentFieldGroup\Supports\FieldGroupConfig::getFieldModelClass();
    }

    public static function getUserTableName(): string
    {
        return app(static::getUserModelClass())->getTable();
    }

    public static function getUserModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\User::class, Models\User::class);

        return self::ensureClassExists($class, 'User model');
    }

    public static function getUserLoginActivityTableName(): string
    {
        return app(static::getUserModelClass())->getTable();
    }

    public static function getUserLoginActivityModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\UserLoginActivity::class, Models\Users\UserLoginActivity::class);

        return self::ensureClassExists($class, 'UserLoginActivity model');
    }

    public static function getRoleTableName(): string
    {
        return app(static::getRoleModelClass())->getTable();
    }

    public static function getRoleModelClass(): string
    {
        $class = app(PermissionRegistrar::class)->getRoleClass();

        return self::ensureClassExists($class, 'Role model');
    }

    public static function getPermissionTableName(): string
    {
        return app(static::getRoleModelClass())->getTable();
    }

    public static function getPermissionModelClass(): string
    {
        $class = app(PermissionRegistrar::class)->getPermissionClass();

        return self::ensureClassExists($class, 'Permission model');
    }

    public static function getLanguageTableName(): string
    {
        return app(static::getLanguageModelClass())->getTable();
    }

    public static function getLanguageModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Language::class, Models\Language::class);

        return self::ensureClassExists($class, 'Language model');
    }

    /**
     * Ensure that a class exists, or throw an exception.
     *
     * @param  string  $class  The fully qualified class name
     * @param  string  $type  A description of the class type (e.g., 'model', 'service')
     * @return string The class name if it exists
     *
     * @throws \Exception If the class does not exist
     */
    protected static function ensureClassExists(string $class, string $type): string
    {
        if (! class_exists($class)) {
            throw new \Exception("The {$type} class '{$class}' does not exist. Please check your configuration.");
        }

        return $class;
    }
}
