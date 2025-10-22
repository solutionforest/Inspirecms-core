<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\TipTapExtensions;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class CmsMediaAssetExtension extends Node
{
    public static $name = 'cmsMediaAsset';

    // // Media type detection patterns
    // const MEDIA_TYPES = [
    //     'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'],
    //     'video' => ['mp4', 'avi', 'mov', 'webm', 'mkv', 'flv', 'wmv', 'm4v'],
    //     'audio' => ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma'],
    //     'pdf' => ['pdf'],
    //     'document' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
    //     'archive' => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'],
    // ];

    public function addOptions(): array
    {
        return [
            'HTMLAttributes' => [
                'class' => 'trix-attachment-mediapicker',
                'style' => 'display: inline-block; ',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parseHTML()
    {
        return [
            [
                'tag' => 'div.trix-attachment-mediapicker',
            ],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function addAttributes(): array
    {
        return [
            'data-mediaasset-id' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-mediaasset-id'),
            ],
            'data-mime-type' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-mime-type'),
            ],
        ];
    }

    /**
     * @param  object  $node
     * @param  array<string, mixed>  $HTMLAttributes
     * @return array<mixed>
     */
    public function renderHTML($node, array $HTMLAttributes = []): array
    {
        $thumb = $node->attrs?->thumbnailUrl && str_starts_with($node->attrs?->mimeType ?? '', 'image/') && ! str_ends_with($node->attrs?->mimeType ?? '', '.svg')
            ? sprintf(
                '<img src="%s" alt="%s" loading="lazy" class="trix-attachment-image trix-attachment-mediapicker-img">',
                htmlspecialchars($node->attrs?->thumbnailUrl ?? ''),
                htmlspecialchars($node->attrs?->title ?? ''),
            )
            : null;

        $innerContent = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" class="trix-attachment-mediapicker-link">%s</a>',
            htmlspecialchars($node->attrs?->url ?? ''),
            $thumb ?? htmlspecialchars($node->attrs?->title ?? '')
        );

        $content = sprintf(
            '<div %s>%s</div>',
            collect(HTML::mergeAttributes(
                $this->options['HTMLAttributes'],
                [
                    ...$HTMLAttributes,
                    'data-mediaasset-id' => $node->attrs?->id ?? null,
                ]
            ))->map(fn ($value, $key) => sprintf('%s="%s"', htmlspecialchars($key), htmlspecialchars($value)))->implode(' '),
            $innerContent
        );

        return [
            'content' => $content,
        ];

    }
}
