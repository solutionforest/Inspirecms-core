<?php

namespace SolutionForest\InspireCms\Models\Contracts;

interface Template
{
    public function isFileCreated(): bool;

    public function createTemplateFile(): void;

    public function getFileFullPath(): string;
}
