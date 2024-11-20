<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Template
{
    public function templateable(): HasMany;

    public function documentTypes(): MorphToMany;

    public function content(): MorphToMany;

    /**
     * Check if the template file has been created.
     *
     * @return bool True if the file is created, false otherwise.
     */
    public function isFileCreated(): bool;

    /**
     * Create the template file.
     */
    public function createTemplateFile(): void;

    /**
     * Get the full path of the template file.
     *
     * @return string The full path of the template file.
     */
    public function getFileFullPath(): string;

    public function getViewFullName(): string;

    public function performTemplatePath(): string;

    /**
     * Preloads the template content before creating a new template.
     *
     * This method is used to set up the initial content for a template before it is created.
     *
     * @param string $content The content to preload into the template.
     * @return self
     */
    public function preloadTemplateContentBeforeCreate(string $content);
}
