<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Enums;

enum BlockCategory: string
{
    case LAYOUT = 'layout';
    case BASIC = 'basic';
    case MEDIA = 'media';
    case INTERACTIVE = 'interactive';
    case UTILITY = 'utility';
    case INTEGRATION = 'integration';
    case ADVANCED = 'advanced';

    public function getLabel(): string
    {
        return match ($this) {
            self::LAYOUT => __('visual-editor::visual-editor.categories.layout'),
            self::BASIC => __('visual-editor::visual-editor.categories.basic'),
            self::MEDIA => __('visual-editor::visual-editor.categories.media'),
            self::INTERACTIVE => __('visual-editor::visual-editor.categories.interactive'),
            self::UTILITY => __('visual-editor::visual-editor.categories.utility'),
            self::INTEGRATION => __('visual-editor::visual-editor.categories.integration'),
            self::ADVANCED => __('visual-editor::visual-editor.categories.advanced'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::LAYOUT => 'heroicon-o-squares-2x2',
            self::BASIC => 'heroicon-o-cube',
            self::MEDIA => 'heroicon-o-photo',
            self::INTERACTIVE => 'heroicon-o-cursor-arrow-rays',
            self::UTILITY => 'heroicon-o-wrench-screwdriver',
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
            self::UTILITY => 5,
            self::INTEGRATION => 6,
            self::ADVANCED => 7,
        };
    }
}
