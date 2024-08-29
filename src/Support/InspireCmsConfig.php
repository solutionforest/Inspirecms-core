<?php

namespace SolutionForest\InspireCms\Support;

use SolutionForest\InspireCms\Models\CmsContent;
use SolutionForest\InspireCms\Models\CmsContentVersion;
use SolutionForest\InspireCms\Models\CmsDocumentType;
use SolutionForest\InspireCms\Models\CmsLanauage;
use SolutionForest\InspireCms\Models\CmsPropertyData;
use SolutionForest\InspireCms\Models\CmsUser;
use SolutionForest\InspireCms\Models\Polymorphic\CmsComponentFieldGroup;
use SolutionForest\InspireCms\Models\Polymorphic\CmsComponentTree;
use SolutionForest\InspireCms\Models\Users\CmsUserLoginActivity;

class InspireCmsConfig
{
    public static function getContentTableName(): string
    {
        return config('inspirecms.models.content.table_name', 'cms_contents');
    }

    public static function getContentModelClass(): string
    {
        $class = config('inspirecms.models.content.fqcn', CmsContent::class);

        return self::ensureClassExists($class, 'CmsContent model');
    }

    public static function getComponentFieldGroupTableName(): string
    {
        return config('inspirecms.models.component_field_group.table_name', 'cms_component_field_groups');
    }

    public static function getComponentFieldGroupModelClass(): string
    {
        $class = config('inspirecms.models.component_field_group.fqcn', CmsComponentFieldGroup::class);

        return self::ensureClassExists($class, 'CmsComponentFieldGroup model');
    }

    public static function getComponentTreeTableName(): string
    {
        return config('inspirecms.models.component_tree.table_name', 'cms_component_field_groups');
    }

    public static function getComponentTreeModelClass(): string
    {
        $class = config('inspirecms.models.component_tree.fqcn', CmsComponentTree::class);

        return self::ensureClassExists($class, 'CmsComponentTree model');
    }

    public static function getPropertyDataTableName(): string
    {
        return config('inspirecms.models.property_data.table_name', 'cms_property_datas');
    }

    public static function getPropertyDataModelClass(): string
    {
        $class = config('inspirecms.models.property_data.fqcn', CmsPropertyData::class);

        return self::ensureClassExists($class, 'CmsPropertyData model');
    }

    public static function getContentVersionTableName(): string
    {
        return config('inspirecms.models.content_version.table_name', 'cms_content_versions');
    }

    public static function getContentVersionModelClass(): string
    {
        $class = config('inspirecms.models.content_version.fqcn', CmsContentVersion::class);

        return self::ensureClassExists($class, 'CmsPropertyData model');
    }

    public static function getDocumentTypeTableName(): string
    {
        return config('inspirecms.models.document_type.table_name', 'cms_document_types');
    }

    public static function getDocumentTypeModelClass(): string
    {
        $class = config('inspirecms.models.document_type.fqcn', CmsDocumentType::class);

        return self::ensureClassExists($class, 'CmsDocumentType model');
    }

    public static function getFieldGroupTableName(): string
    {
        return \SolutionForest\FilamentFieldGroup\Supports\FieldGroupConfig::getFieldGroupTableName();
    }

    public static function getFieldGroupModelClass(): string
    {
        return \SolutionForest\FilamentFieldGroup\Supports\FieldGroupConfig::getFieldGroupModelClass();
    }

    public static function getFieldTableName(): string
    {
        return \SolutionForest\FilamentFieldGroup\Supports\FieldGroupConfig::getFieldTableName();
    }

    public static function getFieldModelClass(): string
    {
        return \SolutionForest\FilamentFieldGroup\Supports\FieldGroupConfig::getFieldModelClass();
    }

    public static function getUserTableName(): string
    {
        return config('inspirecms.models.user.table_name', 'cms_users');
    }

    public static function getUserModelClass(): string
    {
        $class = config('inspirecms.models.user.fqcn', CmsUser::class);

        return self::ensureClassExists($class, 'CmsUser model');
    }

    public static function getUserLoginActivityTableName(): string
    {
        return config('inspirecms.models.user_login_activity.table_name', 'cms_user_login_activities');
    }

    public static function getUserLoginActivityModelClass(): string
    {
        $class = config('inspirecms.models.user_login_activity.fqcn', CmsUserLoginActivity::class);

        return self::ensureClassExists($class, 'CmsUserLoginActivity model');
    }

    public static function getLanguageTableName(): string
    {
        return config('inspirecms.models.language.table_name', 'cms_languages');
    }

    public static function getLanguageModelClass(): string
    {
        $class = config('inspirecms.models.language.fqcn', CmsLanauage::class);

        return self::ensureClassExists($class, 'CmsLanauage model');
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
