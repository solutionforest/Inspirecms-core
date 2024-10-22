<?php

namespace SolutionForest\InspireCms\Base\Enums;

enum DocumentTypeType: string implements Interfaces\DocumentTypeType
{
    case Web = 'web';

    /**
     * To allow inherits field groups from other document types.
     */
    case Inheritance = 'layout';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Web => __('inspirecms::inspirecms.document_type_type.web.label'),
            self::Inheritance => __('inspirecms::inspirecms.document_type_type.inheritance.label'),
            default => null,
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::Web => __('inspirecms::inspirecms.document_type_type.web.description'),
            self::Inheritance => __('inspirecms::inspirecms.document_type_type.inheritance.description'),
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
        return $this == \SolutionForest\InspireCms\Base\Enums\DocumentTypeType::Web;
    }

    /** @inheritDoc*/
    public function canBeInherited(): bool
    {
        return $this == \SolutionForest\InspireCms\Base\Enums\DocumentTypeType::Inheritance;
    }

    /** @inheritDoc*/
    public function canManageChildDocumentTypes(): bool
    {
        return $this == \SolutionForest\InspireCms\Base\Enums\DocumentTypeType::Web;
    }

    /** @inheritDoc*/
    public static function allCanBeInherited(): array
    {
        return [
            self::Inheritance,
        ];
    }

    public static function getDefaultValue(): Interfaces\DocumentTypeType
    {
        return self::Web;
    }
}
