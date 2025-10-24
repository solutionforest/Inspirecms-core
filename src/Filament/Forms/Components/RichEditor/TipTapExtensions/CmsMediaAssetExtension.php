<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\TipTapExtensions;

use Illuminate\Support\Arr;
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
            'data-cmsmediaasset-id' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-cmsmediaasset-id'),
            ],
            'data-media-mime-type' => [
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
        $innerContent = match (true) {
            str_starts_with($node->attrs?->mimeType ?? '', 'image/') =>
            !str_ends_with($node->attrs?->filename ?? '', '.svg')
                ? sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer" class="trix-attachment-mediapicker-link"><img src="%s" alt="%s" loading="lazy" class="trix-attachment-image trix-attachment-mediapicker-img" %s></a>',
                    htmlspecialchars($node->attrs?->url ?? ''),
                    htmlspecialchars($node->attrs?->thumbnailUrl ?? $node->attrs?->url ?? ''),
                    htmlspecialchars($node->attrs?->title ?? ''),
                    implode(' ', $this->convertResponsiveAttributes($node->attrs?->responsive ?? []))
                )
                : sprintf(
                    '<img src="%s" alt="%s" loading="lazy" class="trix-attachment-image trix-attachment-mediapicker-img">',
                    htmlspecialchars($node->attrs?->thumbnailUrl ?? $node->attrs?->url ?? ''),
                    htmlspecialchars($node->attrs?->title ?? ''),
                ),
            str_starts_with($node->attrs?->mimeType ?? '', 'video/') =>
                sprintf(
                    '<video controls src="%s" alt="%s" loading="lazy" class="trix-attachment-video trix-attachment-mediapicker-video"></video>',
                    htmlspecialchars($node->attrs?->url ?? ''),
                    htmlspecialchars($node->attrs?->title ?? ''),
                ),
            str_starts_with($node->attrs?->mimeType ?? '', 'audio/') =>
                sprintf(
                    '<audio controls src="%s" alt="%s" loading="lazy" class="trix-attachment-audio trix-attachment-mediapicker-audio"></audio>',
                    htmlspecialchars($node->attrs?->url ?? ''),
                    htmlspecialchars($node->attrs?->title ?? ''),
                ),
            default => sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" class="trix-attachment-mediapicker-link">%s</a>',
                htmlspecialchars($node->attrs?->url ?? ''),
                htmlspecialchars($node->attrs?->title ?? '')
            ),
        };

        $content = sprintf(
            '<div %s>%s</div>',
            collect(HTML::mergeAttributes(
                $this->options['HTMLAttributes'],
                [
                    ...$HTMLAttributes,
                ]
            ))->map(fn ($value, $key) => sprintf('%s="%s"', htmlspecialchars($key), htmlspecialchars($value)))->implode(' '),
            $innerContent
        );

        return [
            'content' => $content,
        ];

    }

    private function convertResponsiveAttributes($elements)
    {
        $responsiveData = [];
        if ($elements instanceof \stdClass) {
            $elements = json_decode(json_encode($elements), true);
        } else if (is_string($elements)) {
            $elements = json_decode($elements, true);
        } 
        if (!is_array($elements)) {
            return $responsiveData;
        }
        foreach ($elements as $key => $value) {
            $responsiveData["data-image-responsive__{$key}"] = $value;
        }
        return $responsiveData;
    }
}
