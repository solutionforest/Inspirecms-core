<?php

namespace SolutionForest\InspireCms\Models\Contracts;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
interface Template
{
    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function templateable();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function documentTypes();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function content();

    /**
     * Check if the template file has been created.
     *
     * @return bool True if the file is created, false otherwise.
     */
    public function isFileCreated();

    /**
     * Create the template file.
     *
     * @return void
     */
    public function createTemplateFile();

    /**
     * Get the full path of the template file.
     *
     * @return string The full path of the template file.
     */
    public function getFileFullPath();

    /**
     * Get the full name of the view.
     *
     * @return string The full name of the view.
     */
    public function getViewFullName();

    /**
     * Perform operations to retrieve the template path.
     *
     * @return string The path of the template.
     */
    public function retrieveTemplatePath();

    /**
     * Preloads the template content before creating a new template.
     *
     * This method is used to set up the initial content for a template before it is created.
     *
     * @param  string  $content  The content to preload into the template.
     * @return TModel
     */
    public function preloadTemplateContentBeforeCreate($content);
}
