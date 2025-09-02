<?php

namespace SolutionForest\InspireCms\Base\Enums;

use SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory as InterfacesDocumentTypeCategory;

enum DocumentTypeCategory: string implements InterfacesDocumentTypeCategory
{
    case Web = 'web';

    case Data = 'data';

    // /**
    //  * To allow inherits field groups from other document types.
    //  */
    // case Inheritance = 'inheritance';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Web => __('inspirecms::resources/document-type.categories.web.label'),
            self::Data => __('inspirecms::resources/document-type.categories.data.label'),
            // self::Inheritance => __('inspirecms::resources/document-type.categories.inheritance.label'),
            default => null,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::Web => __('inspirecms::resources/document-type.categories.web.description'),
            self::Data => __('inspirecms::resources/document-type.categories.data.description'),
            // self::Inheritance => __('inspirecms::resources/document-type.categories.inheritance.description'),
            default => null,
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Web => 'success',
            self::Data => 'warning',
            // self::Inheritance => 'info',
            default => null,
        };
    }

    /** @inheritDoc*/
    public function canInheriting(): bool
    {
        return true;
        // return $this != \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Inheritance;
    }

    /** @inheritDoc*/
    public function canBeInherited(): bool
    {
        return false;
        // return $this == \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Inheritance;
    }

    /** @inheritDoc*/
    public static function allCanBeInherited(): array
    {
        return [
            // self::Inheritance,
        ];
    }

    public static function getDefaultValue(): InterfacesDocumentTypeCategory
    {
        return self::Web;
    }
}
