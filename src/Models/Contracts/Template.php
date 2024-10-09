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
}
