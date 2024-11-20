<?php

namespace SolutionForest\InspireCms\Services;

interface ImportDataServiceInterface
{
    /**
     * Adds a new document type to the system.
     *
     * @param  string  $name  The name of the document type.
     * @param  array<string,array>  $fieldGroups  The field groups associated with the document type.
     * @param  array<string,?string>|string  $templates  The templates associated with the document type.
     * @param  string[]|string|null  $inheritanceDocumentTypes  The document types from which this document type inherits.
     * @param  bool  $childrenAsTable  Whether the children should be displayed as a table.
     * @param  string  $category  The category of the document type.
     * @param  string|null  $title  The title of the document type (optional).
     * @param  string|null  $parent  The parent document type (optional).
     * @return void
     */
    public function addDocumentType(string $name, array $fieldGroups, array | string $templates, array | string | null $inheritanceDocumentTypes, bool $childrenAsTable, string $category, ?string $title = null, ?string $parent = null);

    /**
     * Adds a field group to the system.
     *
     * @param  string  $name  The name of the field group.
     * @param  array<string,array>  $fields  An array of fields to be included in the group.
     * @param  string|null  $title  An optional title for the field group.
     * @return void
     */
    public function addFieldGroup(string $name, array $fields, ?string $title = null);

    /**
     * Adds a field to the system.
     *
     * @param  string  $name  The name of the field.
     * @param  string  $group  The group to which the field belongs.
     * @param  array  $data  The data associated with the field.
     * @param  string|null  $label  An optional label for the field.
     * @return void
     */
    public function addField(string $name, string $group, array $data, ?string $label = null);

    /**
     * Adds a template with the specified slug and content.
     *
     * @param  string  $slug  The unique identifier for the template.
     * @param  ?string  $content  The content of the template. Default is null.
     * @return void
     */
    public function addTemplate(string $slug, $content = null);

    /**
     * Adds content to the system.
     *
     * @param  string  $slug  The unique identifier for the content.
     * @param  array<string,string>  $title  The title of the content.
     * @param  string  $documentType  The type of document.
     * @param  array<string,mixed>  $propertyData  An array of properties associated with the content.
     * @param  string  $publishState  The publish state of the content.
     * @param  array  $sitemap  An optional array for sitemap settings.
     * @param  array  $webSetting  An optional array for web settings.
     * @param  string|null  $parent  An optional parent identifier.
     * @param  string|null  $template  An optional template identifier.
     * @return void
     */
    public function addContent(string $slug, $title, string $documentType, array $propertyData, string $publishState, array $sitemap = [], array $webSetting = [], ?string $parent = null, ?string $template = null);

    /**
     * Adds a navigation item to the system.
     *
     * @param  string  $category  The category of the navigation item.
     * @param  string  $type  The type of the navigation item.
     * @param  array<string,string>  $title  The title of the navigation item.
     * @param  string|null  $contentFullSlug  The full slug of the content, if applicable.
     * @param  string|null  $url  The URL of the navigation item, if applicable.
     * @param  string|null  $target  The target attribute specifying where to open the linked document, if applicable.
     * @return void
     */
    public function addNavigation(string $category, string $type, $title, ?string $contentFullSlug = null, ?string $url = null, ?string $target = null);

    /**
     * Executes the import data service.
     *
     * This method is responsible for running the import data process.
     *
     * @return void
     */
    public function run();

    /**
     * Resets the import data service to its initial state.
     *
     * This method clears any stored data and prepares the service for a new import operation.
     *
     * @return void
     */
    public function reset();

    public function hasErrors(): bool;

    public function getErrors(): array;
}
