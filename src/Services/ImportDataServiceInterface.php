<?php

namespace SolutionForest\InspireCms\Services;

use SolutionForest\InspireCms\ImportData\Entities\Content as EntitiesContent;
use SolutionForest\InspireCms\ImportData\Entities\DocumentType as EntitiesDocumentType;
use SolutionForest\InspireCms\ImportData\Entities\FieldGroup as EntitiesFieldGroup;
use SolutionForest\InspireCms\ImportData\Entities\Language as EntitiesLanguage;
use SolutionForest\InspireCms\ImportData\Entities\Navigation as EntitiesNavigation;
use SolutionForest\InspireCms\ImportData\Entities\Template as EntitiesTemplate;

interface ImportDataServiceInterface
{
    /**
     * Adds a new document type.
     *
     * @param  EntitiesDocumentType  $data  The document type data to be added.
     * @return void
     */
    public function addDocumentType(EntitiesDocumentType $data);

    /**
     * Adds a field group to the system.
     *
     * @param  EntitiesFieldGroup  $data  The field group entity containing the data.
     * @return void
     */
    public function addFieldGroup(EntitiesFieldGroup $data);

    /**
     * Adds a template to the system.
     *
     * @param  string  $slug  The unique identifier for the template.
     * @param  EntitiesTemplate  $data  The template data to be added.
     * @return void
     */
    public function addTemplate(EntitiesTemplate $data);

    /**
     * Adds content to the system.
     *
     * @param  EntitiesContent  $data  The content data to be added.
     * @return void
     */
    public function addContent(EntitiesContent $data);

    /**
     * Adds a navigation entity to the system.
     *
     * @param  EntitiesNavigation  $data  The navigation entity to be added.
     * @return void
     */
    public function addNavigation(EntitiesNavigation $data);

    /**
     * Adds a language to the system.
     *
     * @param  EntitiesLanguage  $data  The language entity to be added.
     * @return void
     */
    public function addLanguage(EntitiesLanguage $data);

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
