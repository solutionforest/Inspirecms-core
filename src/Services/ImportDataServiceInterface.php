<?php

namespace SolutionForest\InspireCms\Services;

use SolutionForest\InspireCms\ImportData\Entities;

interface ImportDataServiceInterface
{
    /**
     * Adds a new document type.
     *
     * @param  Entities\DocumentType  $data  The document type data to be added.
     * @return void
     */
    public function addDocumentType(Entities\DocumentType $data);

    /**
     * Adds a field group to the system.
     *
     * @param  Entities\FieldGroup  $data  The field group entity containing the data.
     * @return void
     */
    public function addFieldGroup(Entities\FieldGroup $data);

    /**
     * Adds a template to the system.
     *
     * @param  string  $slug  The unique identifier for the template.
     * @param  Entities\Template  $data  The template data to be added.
     * @return void
     */
    public function addTemplate(Entities\Template $data);

    /**
     * Adds content to the system.
     *
     * @param  Entities\Content  $data  The content data to be added.
     * @return void
     */
    public function addContent(Entities\Content $data);

    /**
     * Adds a navigation entity to the system.
     *
     * @param  Entities\Navigation  $data  The navigation entity to be added.
     * @return void
     */
    public function addNavigation(Entities\Navigation $data);

    /**
     * Adds a language to the system.
     *
     * @param  Entities\Language  $data  The language entity to be added.
     * @return void
     */
    public function addLanguage(Entities\Language $data);

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

    /**
     * Checks if there are any errors.
     *
     * @return bool True if there are errors, false otherwise.
     */
    public function hasErrors(): bool;

    /**
     * Retrieve an array of errors.
     *
     * @return array An array containing error messages.
     */
    public function getErrors(): array;

    /**
     * Retrieve the validation errors.
     *
     * @return array An array of validation error messages.
     */
    public function getValidationErrors(): array;

    /**
     * Validates the necessary conditions before running the import data service.
     *
     * @return bool Returns true if the validation is successful, otherwise false.
     */
    public function validateBeforeRun(): bool;
}
