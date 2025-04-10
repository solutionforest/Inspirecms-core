<?php

namespace SolutionForest\InspireCms;

use SolutionForest\InspireCms\Facades\ModelManifest;
use SolutionForest\InspireCms\Support\Models as SupportModels;
use Spatie\Permission\PermissionRegistrar;

/**
 * @phpstan-type ModelClass class-string<\Illuminate\Database\Eloquent\Model>
 */
class InspireCmsConfig
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return config("inspirecms.{$key}", $default);
    }

    public static function set(string $key, mixed $value): void
    {
        config()->set("inspirecms.{$key}", $value);
    }

    public static function getPanelId(): string
    {
        return static::get('admin.panel_id', 'inspirecms');
    }

    /**
     * Get a Filament resource by its key.
     *
     * @param  string  $key  The key of the resource to retrieve
     * @param  null|class-string<\Filament\Resources\Resource>  $default  The default value to return if the resource is not found
     * @return null|class-string<\Filament\Resources\Resource>
     */
    public static function getFilamentResource($key, $default = null)
    {
        return collect(static::getFilamentResources())->get($key, $default);
    }

    /**
     * @return array<string,class-string<\Filament\Resources\Resource>>
     */
    public static function getFilamentResources()
    {
        return collect(static::get('admin.resources', []))
            ->where(fn ($class) => is_string($class) && class_exists($class) && is_a($class, \Filament\Resources\Resource::class, true))
            ->toArray();
    }

    /**
     * Get a Filament page configuration value by key
     *
     * @param  string  $key  The configuration key to retrieve
     * @param  null|class-string<\Filament\Pages\Page>  $default  The default value to return if the key doesn't exist
     * @return null|class-string<\Filament\Pages\Page> The configuration value or the default value if not found
     */
    public static function getFilamentPage($key, $default = null)
    {
        return collect(static::getFilamentPages())->get($key, $default);
    }

    /**
     * @return array<string,class-string<\Filament\Pages\Page>>
     */
    public static function getFilamentPages()
    {
        return collect(static::get('admin.pages', []))
            ->where(fn ($class) => is_string($class) && class_exists($class) && is_a($class, \Filament\Pages\Page::class, true))
            ->toArray();
    }

    /**
     * @return array<string,class-string<\Clusters\Clusters\Cluster>>
     */
    public static function getFilamentClusters()
    {
        return collect(static::get('admin.clusters', []))
            ->where(fn ($class) => is_string($class) && class_exists($class) && is_a($class, \Filament\Clusters\Cluster::class, true))
            ->toArray();
    }

    public static function getContentTableName(): string
    {
        return app(static::getContentModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getContentModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Content::class, Models\Content::class);

        return self::ensureClassExists($class, 'Content model');
    }

    public static function getContentPathTableName(): string
    {
        return app(static::getContentPathModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getContentPathModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ContentPath::class, Models\ContentPath::class);

        return self::ensureClassExists($class, 'ContentPath model');
    }

    public static function getContentLockTableName(): string
    {
        return app(static::getContentLockModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getContentLockModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ContentLock::class, Models\ContentLock::class);

        return self::ensureClassExists($class, 'ContentLock model');
    }

    public static function getContentRouteTableName(): string
    {
        return app(static::getContentRouteModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getContentRouteModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ContentRoute::class, Models\ContentRoute::class);

        return self::ensureClassExists($class, 'ContentRoute model');
    }

    public static function getFieldGroupableTableName(): string
    {
        return app(static::getFieldGroupableModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getFieldGroupableModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\FieldGroupable::class, Models\Polymorphic\FieldGroupable::class);

        return self::ensureClassExists($class, 'FieldGroupable model');
    }

    public static function getNestableTreeTableName(): string
    {
        return app(static::getNestableTreeModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getNestableTreeModelClass(): string
    {
        $class = ModelManifest::get(SupportModels\Contracts\NestableTree::class, SupportModels\Polymorphic\NestableTree::class);

        return self::ensureClassExists($class, 'NestableTree model');
    }

    public static function getContentVersionTableName(): string
    {
        return app(static::getContentVersionModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getContentVersionModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ContentVersion::class, Models\ContentVersion::class);

        return self::ensureClassExists($class, 'ContentVersion model');
    }

    public static function getContentPublishVersionTableName(): string
    {
        return app(static::getContentPublishVersionModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getContentPublishVersionModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ContentPublishVersion::class, Models\ContentPublishVersion::class);

        return self::ensureClassExists($class, 'ContentPublishVersion model');
    }

    public static function getContentWebSettingTableName(): string
    {
        return app(static::getContentWebSettingModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getContentWebSettingModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\ContentWebSetting::class, Models\ContentWebSetting::class);

        return self::ensureClassExists($class, 'ContentWebSetting model');
    }

    public static function getSitemapTableName(): string
    {
        return app(static::getSitemapModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getSitemapModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Sitemap::class, Models\Sitemap::class);

        return self::ensureClassExists($class, 'Sitemap model');
    }

    public static function getDocumentTypeTableName(): string
    {
        return app(static::getDocumentTypeModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getDocumentTypeModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\DocumentType::class, Models\DocumentType::class);

        return self::ensureClassExists($class, 'DocumentType model');
    }

    public static function getDocumentTypeInheritanceTableName(): string
    {
        return app(static::getDocumentTypeInheritanceModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getDocumentTypeInheritanceModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\DocumentTypeInheritance::class, Models\Pivot\DocumentTypeInheritance::class);

        return self::ensureClassExists($class, 'DocumentTypeInheritance model');
    }

    public static function getAllowedDocumentTypeTableName(): string
    {
        return app(static::getAllowedDocumentTypeModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getAllowedDocumentTypeModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\AllowedDocumentType::class, Models\Pivot\AllowedDocumentType::class);

        return self::ensureClassExists($class, 'AllowedDocumentType model');
    }

    public static function getFieldGroupTableName(): string
    {
        return app(static::getFieldGroupModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getFieldGroupModelClass(): string
    {
        $class = \SolutionForest\FilamentFieldGroup\Supports\FieldGroupConfig::getFieldGroupModelClass();

        self::ensureClassExists($class, 'FieldGroup model');

        // Ensure the model implements the FieldGroup contract
        if (! in_array(Models\Contracts\FieldGroup::class, class_implements($class))) {
            throw new \Exception("The FieldGroup model '{$class}' must implement the FieldGroup contract.");
        }

        return $class;
    }

    public static function getFieldTableName(): string
    {
        return app(static::getFieldModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getFieldModelClass(): string
    {
        $class = \SolutionForest\FilamentFieldGroup\Supports\FieldGroupConfig::getFieldModelClass();

        static::ensureClassExists($class, 'Field model');

        // Ensure the model implements the Field contract
        if (! in_array(Models\Contracts\Field::class, class_implements($class))) {
            throw new \Exception("The Field model '{$class}' must implement the Field contract.");
        }

        return $class;
    }

    public static function getUserTableName(): string
    {
        return app(static::getUserModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getUserModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\User::class, Models\User::class);

        return self::ensureClassExists($class, 'User model');
    }

    public static function getUserLoginActivityTableName(): string
    {
        return app(static::getUserModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getUserLoginActivityModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\UserLoginActivity::class, Models\Users\UserLoginActivity::class);

        return self::ensureClassExists($class, 'UserLoginActivity model');
    }

    public static function getRoleTableName(): string
    {
        return app(static::getRoleModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getRoleModelClass(): string
    {
        $class = app(PermissionRegistrar::class)->getRoleClass();

        return self::ensureClassExists($class, 'Role model');
    }

    public static function getPermissionTableName(): string
    {
        return app(static::getRoleModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getPermissionModelClass(): string
    {
        $class = app(PermissionRegistrar::class)->getPermissionClass();

        return self::ensureClassExists($class, 'Permission model');
    }

    public static function getLanguageTableName(): string
    {
        return app(static::getLanguageModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getLanguageModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Language::class, Models\Language::class);

        return self::ensureClassExists($class, 'Language model');
    }

    public static function getTemplateTableName(): string
    {
        return app(static::getTemplateModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getTemplateModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Template::class, Models\Template::class);

        return self::ensureClassExists($class, 'Template model');
    }

    public static function getTemplateableTableName(): string
    {
        return app(static::getTemplateableModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getTemplateableModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Templateable::class, Models\Polymorphic\Templateable::class);

        return self::ensureClassExists($class, 'Templateable model');
    }

    public static function getMediaAssetTableName(): string
    {
        return app(static::getMediaAssetModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getMediaAssetModelClass(): string
    {
        $class = ModelManifest::get(SupportModels\Contracts\MediaAsset::class, SupportModels\MediaAsset::class);

        return self::ensureClassExists($class, 'MediaAsset model');
    }

    public static function getKeyValueTableName(): string
    {
        return app(static::getKeyValueModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getKeyValueModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\KeyValue::class, Models\KeyValue::class);

        return self::ensureClassExists($class, 'KeyValue model');
    }

    public static function getNavigationTableName(): string
    {
        return app(static::getNavigationModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getNavigationModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Navigation::class, Models\Navigation::class);

        return self::ensureClassExists($class, 'Navigation model');
    }

    public static function getImportTableName(): string
    {
        return app(static::getImportModelClass())->getTable();
    }

    /**
     * @return ModelClass
     */
    public static function getImportModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Import::class, Models\Import::class);

        return self::ensureClassExists($class, 'Import model');
    }

    public static function getExportTableName(): string
    {
        return app(static::getExportModelClass())->getTable();
    }

    public static function getExportModelClass(): string
    {
        $class = ModelManifest::get(Models\Contracts\Export::class, Models\Export::class);

        return self::ensureClassExists($class, 'Export model');
    }

    /**
     * Ensure that a class exists, or throw an exception.
     *
     * @param  class-string  $class  The fully qualified class name
     * @param  string  $type  A description of the class type (e.g., 'model', 'service')
     * @return class-string The class name if it exists
     *
     * @throws \Exception If the class does not exist
     */
    protected static function ensureClassExists($class, string $type)
    {
        if (! class_exists($class)) {
            throw new \Exception("The {$type} class '{$class}' does not exist. Please check your configuration.");
        }

        return $class;
    }
}
