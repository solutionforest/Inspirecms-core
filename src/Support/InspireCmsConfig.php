<?php

namespace SolutionForest\InspireCms\Support;

use SolutionForest\InspireCms\Models\CmsDocumentType;
use SolutionForest\InspireCms\Models\CmsPage;
use SolutionForest\InspireCms\Models\Polymorphic\CmsComponentFieldGroup;
use SolutionForest\InspireCms\Models\Polymorphic\CmsComponentTree;
use SolutionForest\InspireCms\Models\Polymorphic\CmsComponentVersion;

class InspireCmsConfig
{
    public static function getPageTableName(): string
    {
        return config('inspirecms.models.page.table_name', 'cms_pages');
    }

    public static function getPageModelClass(): string
    {
        $class = config('inspirecms.models.page.fqcn', CmsPage::class);

        return self::ensureClassExists($class, 'CmsPage model');
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

    public static function getComponentVersionTableName(): string
    {
        return config('inspirecms.models.component_version.table_name', 'cms_component_versions');
    }

    public static function getComponentVersionModelClass(): string
    {
        $class = config('inspirecms.models.component_version.fqcn', CmsComponentVersion::class);

        return self::ensureClassExists($class, 'CmsComponentVersion model');
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
