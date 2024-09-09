<?php

namespace SolutionForest\InspireCms\Models\Contracts;

interface Template
{
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
}
