<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Blocks\Types;

use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class EmbedBlock extends AbstractBlock
{
    public function getType(): string
    {
        return 'embed';
    }

    public function getLabel(): string
    {
        return 'Embed';
    }

    public function getCategory(): string
    {
        return BlockCategory::MEDIA->value;
    }

    public function getIcon(): string
    {
        return 'heroicon-o-code-bracket';
    }

    public function getDescription(): string
    {
        return 'Embed external content like maps, forms, or social media';
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'embedType',
                'type' => 'select',
                'label' => 'Embed Type',
                'options' => [
                    'iframe' => 'iFrame URL',
                    'html' => 'HTML Code',
                    'oembed' => 'oEmbed URL',
                ],
                'default' => 'iframe',
            ],
            [
                'name' => 'url',
                'type' => 'text',
                'label' => 'URL',
                'placeholder' => 'https://...',
                'description' => 'URL to embed (for iframe or oEmbed)',
            ],
            [
                'name' => 'html',
                'type' => 'code',
                'label' => 'HTML Code',
                'description' => 'Paste embed code here',
                'language' => 'html',
            ],
            [
                'name' => 'width',
                'type' => 'text',
                'label' => 'Width',
                'placeholder' => '100%',
                'default' => '100%',
            ],
            [
                'name' => 'height',
                'type' => 'text',
                'label' => 'Height',
                'placeholder' => '400px',
                'default' => '400px',
            ],
            [
                'name' => 'aspectRatio',
                'type' => 'select',
                'label' => 'Aspect Ratio',
                'options' => [
                    '' => 'None (use fixed height)',
                    '16/9' => '16:9',
                    '4/3' => '4:3',
                    '1/1' => '1:1',
                ],
                'default' => '',
            ],
            [
                'name' => 'allowFullscreen',
                'type' => 'toggle',
                'label' => 'Allow Fullscreen',
                'default' => true,
            ],
            [
                'name' => 'lazyLoad',
                'type' => 'toggle',
                'label' => 'Lazy Load',
                'default' => true,
            ],
            [
                'name' => 'title',
                'type' => 'text',
                'label' => 'Title (Accessibility)',
                'description' => 'Title for the iframe element',
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
            'embedType' => 'iframe',
            'url' => '',
            'html' => '',
            'width' => '100%',
            'height' => '400px',
            'aspectRatio' => '',
            'allowFullscreen' => true,
            'lazyLoad' => true,
            'title' => '',
            'caption' => '',
        ];
    }

    public function validateProps(array $props): array
    {
        $errors = [];
        $embedType = $props['embedType'] ?? 'iframe';

        if ($embedType === 'html' && empty($props['html'])) {
            $errors['html'] = 'HTML code is required';
        }

        if (in_array($embedType, ['iframe', 'oembed']) && empty($props['url'])) {
            $errors['url'] = 'URL is required';
        }

        return $errors;
    }
}
