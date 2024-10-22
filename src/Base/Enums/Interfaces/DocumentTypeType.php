<?php

namespace SolutionForest\InspireCms\Base\Enums\Interfaces;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

interface DocumentTypeType extends HasColor, HasDescription, HasLabel
{
    public function canInheriting(): bool;

    /**
     * Determines if the document type can be inherited.
     *
     * @return bool True if the document type can be inherited, false otherwise.
     */
    public function canBeInherited(): bool;

    /**
     * Determines if the current document type can manage child document types.
     *
     * @return bool True if the current document type can manage child document types, false otherwise.
     */
    public function canManageChildDocumentTypes(): bool;

    /**
     * Retrieve an array of all document types that can be inherited.
     *
     * @return array An array of document types that can be inherited.
     */
    public static function allCanBeInherited(): array;

    public static function getDefaultValue(): DocumentTypeType;
}
