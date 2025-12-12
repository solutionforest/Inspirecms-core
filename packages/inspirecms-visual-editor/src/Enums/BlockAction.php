<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Enums;

enum BlockAction: string
{
    case ADD_BEFORE = 'add_before';
    case ADD_AFTER = 'add_after';
    case ADD_CHILD = 'add_child';
    case DUPLICATE = 'duplicate';
    case COPY = 'copy';
    case PASTE = 'paste';
    case CUT = 'cut';
    case DELETE = 'delete';
    case MOVE_UP = 'move_up';
    case MOVE_DOWN = 'move_down';
    case WRAP = 'wrap';
    case UNWRAP = 'unwrap';

    public function getLabel(): string
    {
        return match ($this) {
            self::ADD_BEFORE => __('inspirecms::visual-editor.actions.add_before'),
            self::ADD_AFTER => __('inspirecms::visual-editor.actions.add_after'),
            self::ADD_CHILD => __('inspirecms::visual-editor.actions.add_child'),
            self::DUPLICATE => __('inspirecms::visual-editor.actions.duplicate'),
            self::COPY => __('inspirecms::visual-editor.actions.copy'),
            self::PASTE => __('inspirecms::visual-editor.actions.paste'),
            self::CUT => __('inspirecms::visual-editor.actions.cut'),
            self::DELETE => __('inspirecms::visual-editor.actions.delete'),
            self::MOVE_UP => __('inspirecms::visual-editor.actions.move_up'),
            self::MOVE_DOWN => __('inspirecms::visual-editor.actions.move_down'),
            self::WRAP => __('inspirecms::visual-editor.actions.wrap'),
            self::UNWRAP => __('inspirecms::visual-editor.actions.unwrap'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ADD_BEFORE => 'heroicon-o-arrow-up-on-square',
            self::ADD_AFTER => 'heroicon-o-arrow-down-on-square',
            self::ADD_CHILD => 'heroicon-o-plus',
            self::DUPLICATE => 'heroicon-o-document-duplicate',
            self::COPY => 'heroicon-o-clipboard-document',
            self::PASTE => 'heroicon-o-clipboard',
            self::CUT => 'heroicon-o-scissors',
            self::DELETE => 'heroicon-o-trash',
            self::MOVE_UP => 'heroicon-o-arrow-up',
            self::MOVE_DOWN => 'heroicon-o-arrow-down',
            self::WRAP => 'heroicon-o-rectangle-group',
            self::UNWRAP => 'heroicon-o-rectangle-stack',
        };
    }

    public function getKeyboardShortcut(): ?string
    {
        return match ($this) {
            self::DUPLICATE => 'Ctrl+D',
            self::COPY => 'Ctrl+C',
            self::PASTE => 'Ctrl+V',
            self::CUT => 'Ctrl+X',
            self::DELETE => 'Delete',
            default => null,
        };
    }
}
