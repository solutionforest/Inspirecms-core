<?php

namespace SolutionForest\InspireCms\Base\Enums;

enum DocumentTypeCategory: string implements Interfaces\DocumentTypeCategory
{
    case Web = 'web';

    /**
     * To allow inherits field groups from other document types.
     */
    case Inheritance = 'inheritance';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Web => __('inspirecms::resources/document-type.categories.web.label'),
            self::Inheritance => __('inspirecms::resources/document-type.categories.inheritance.label'),
            default => null,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::Web => __('inspirecms::resources/document-type.categories.web.description'),
            self::Inheritance => __('inspirecms::resources/document-type.categories.inheritance.description'),
            default => null,
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Web => 'primary',
            self::Inheritance => 'info',
            default => null,
        };
    }

    /** @inheritDoc*/
    public function canInheriting(): bool
    {
        return $this == \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Web;
    }

    /** @inheritDoc*/
    public function canBeInherited(): bool
    {
        return $this == \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Inheritance;
    }

    /** @inheritDoc*/
    public function canManageChildDocumentTypes(): bool
    {
        return $this == \SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory::Web;
    }

    /** @inheritDoc*/
    public static function allCanBeInherited(): array
    {
        return [
            self::Inheritance,
        ];
    }

    public static function getDefaultValue(): Interfaces\DocumentTypeCategory
    {
        return self::Web;
    }
}
