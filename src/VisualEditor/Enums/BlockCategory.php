<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Enums;

enum BlockCategory: string
{
    case LAYOUT = 'layout';
    case BASIC = 'basic';
    case MEDIA = 'media';
    case INTERACTIVE = 'interactive';
    case INTEGRATION = 'integration';
    case ADVANCED = 'advanced';

    public function getLabel(): string
    {
        return match ($this) {
            self::LAYOUT => __('inspirecms::visual-editor.categories.layout'),
            self::BASIC => __('inspirecms::visual-editor.categories.basic'),
            self::MEDIA => __('inspirecms::visual-editor.categories.media'),
            self::INTERACTIVE => __('inspirecms::visual-editor.categories.interactive'),
            self::INTEGRATION => __('inspirecms::visual-editor.categories.integration'),
            self::ADVANCED => __('inspirecms::visual-editor.categories.advanced'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::LAYOUT => 'heroicon-o-squares-2x2',
            self::BASIC => 'heroicon-o-cube',
            self::MEDIA => 'heroicon-o-photo',
            self::INTERACTIVE => 'heroicon-o-cursor-arrow-rays',
            self::INTEGRATION => 'heroicon-o-link',
            self::ADVANCED => 'heroicon-o-code-bracket',
        };
    }

    public function getOrder(): int
    {
        return match ($this) {
            self::LAYOUT => 1,
            self::BASIC => 2,
            self::MEDIA => 3,
            self::INTERACTIVE => 4,
            self::INTEGRATION => 5,
            self::ADVANCED => 6,
        };
    }
}
