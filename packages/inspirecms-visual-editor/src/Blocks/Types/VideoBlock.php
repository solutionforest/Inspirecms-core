<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Types;

use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class VideoBlock extends AbstractBlock
{
    public function getType(): string
    {
        return 'video';
    }

    public function getLabel(): string
    {
        return 'Video';
    }

    public function getCategory(): string
    {
        return BlockCategory::MEDIA->value;
    }

    public function getIcon(): string
    {
        return 'heroicon-o-play-circle';
    }

    public function getDescription(): string
    {
        return 'Embed YouTube, Vimeo, or self-hosted videos';
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'source',
                'type' => 'select',
                'label' => 'Video Source',
                'options' => [
                    'youtube' => 'YouTube',
                    'vimeo' => 'Vimeo',
                    'self' => 'Self-hosted',
                ],
                'default' => 'youtube',
            ],
            [
                'name' => 'url',
                'type' => 'text',
                'label' => 'Video URL',
                'placeholder' => 'https://www.youtube.com/watch?v=...',
            ],
            [
                'name' => 'videoId',
                'type' => 'text',
                'label' => 'Video ID',
                'description' => 'YouTube or Vimeo video ID',
            ],
            [
                'name' => 'autoplay',
                'type' => 'toggle',
                'label' => 'Autoplay',
                'default' => false,
            ],
            [
                'name' => 'muted',
                'type' => 'toggle',
                'label' => 'Muted',
                'default' => false,
            ],
            [
                'name' => 'loop',
                'type' => 'toggle',
                'label' => 'Loop',
                'default' => false,
            ],
            [
                'name' => 'controls',
                'type' => 'toggle',
                'label' => 'Show Controls',
                'default' => true,
            ],
            [
                'name' => 'aspectRatio',
                'type' => 'select',
                'label' => 'Aspect Ratio',
                'options' => [
                    '16/9' => '16:9 (Widescreen)',
                    '4/3' => '4:3 (Standard)',
                    '1/1' => '1:1 (Square)',
                    '9/16' => '9:16 (Vertical)',
                ],
                'default' => '16/9',
            ],
            [
                'name' => 'poster',
                'type' => 'image',
                'label' => 'Poster Image',
                'description' => 'Thumbnail shown before video plays (self-hosted only)',
            ],
            [
                'name' => 'caption',
                'type' => 'text',
                'label' => 'Caption',
            ],
        ];
    }

    public function getDefaultProps(): array
    {
        return [
            'source' => 'youtube',
            'url' => '',
            'videoId' => '',
            'autoplay' => false,
            'muted' => false,
            'loop' => false,
            'controls' => true,
            'aspectRatio' => '16/9',
            'poster' => '',
            'caption' => '',
        ];
    }

    public function validateProps(array $props): array
    {
        $errors = [];

        if (empty($props['url']) && empty($props['videoId'])) {
            $errors['url'] = 'Video URL or ID is required';
        }

        return $errors;
    }
}
