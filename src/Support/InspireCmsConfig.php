<?php

namespace SolutionForest\InspireCms\Support;

use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Models;
use SolutionForest\InspireCms\Support\Models as SupportModels;
use Spatie\Permission\PermissionRegistrar;

class InspireCmsConfig
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return config("inspirecms.{$key}", $default);
    }

    public static function getGuardName(): string
    {
        return static::get('auth.guard', 'inspirecms');
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

    public static function getFieldGroupableTableName(): string
    {
        return app(static::getFieldGroupableModelClass())->getTable();
    }

    public static function getFieldGroupableModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\FieldGroupable::class, Models\Polymorphic\FieldGroupable::class);

        return self::ensureClassExists($class, 'FieldGroupable model');
    }

    public static function getNestableTreeTableName(): string
    {
        return app(static::getNestableTreeModelClass())->getTable();
    }

    public static function getNestableTreeModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\NestableTree::class, Models\Polymorphic\NestableTree::class);

        return self::ensureClassExists($class, 'NestableTree model');
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

    public static function getContentPublishVersionTableName(): string
    {
        return app(static::getContentPublishVersionModelClass())->getTable();
    }

    public static function getContentPublishVersionModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ContentPublishVersion::class, Models\ContentPublishVersion::class);

        return self::ensureClassExists($class, 'ContentPublishVersion model');
    }

    public static function getContentWebSettingTableName(): string
    {
        return app(static::getContentWebSettingModelClass())->getTable();
    }

    public static function getContentWebSettingModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ContentWebSetting::class, Models\ContentWebSetting::class);

        return self::ensureClassExists($class, 'ContentWebSetting model');
    }

    public static function getSiteMapTableName(): string
    {
        return app(static::getSiteMapModelClass())->getTable();
    }

    public static function getSiteMapModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\SiteMap::class, Models\SiteMap::class);

        return self::ensureClassExists($class, 'SiteMap model');
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

    public static function getTemplateTableName(): string
    {
        return app(static::getTemplateModelClass())->getTable();
    }

    public static function getTemplateModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Template::class, Models\Template::class);

        return self::ensureClassExists($class, 'Template model');
    }

    public static function getTemplateableTableName(): string
    {
        return app(static::getTemplateableModelClass())->getTable();
    }

    public static function getTemplateableModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Templateable::class, Models\Polymorphic\Templateable::class);

        return self::ensureClassExists($class, 'Templateable model');
    }

    public static function getMediaAssetTableName(): string
    {
        return app(static::getMediaAssetModelClass())->getTable();
    }

    public static function getMediaAssetModelClass(): string
    {
        $class = ModelManifest::get(SupportModels\Contracts\MediaAsset::class, SupportModels\MediaAsset::class);

        return self::ensureClassExists($class, 'MediaAsset model');
    }

    public static function getNavigationTableName(): string
    {
        return app(static::getNavigationModelClass())->getTable();
    }

    public static function getNavigationModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Navigation::class, Models\Navigation::class);

        return self::ensureClassExists($class, 'Navigation model');
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
