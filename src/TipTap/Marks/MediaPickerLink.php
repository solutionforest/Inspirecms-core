<?php

namespace SolutionForest\InspireCms\TipTap\Marks;

use Tiptap\Core\Mark;

/**
 * MediaPickerLink TipTap Extension for PHP
 * Provides server-side functionality for media picker links
 */
class MediaPickerLink extends Mark
{
    public static $name = 'mediaPickerLink';

    // Media type detection patterns
    const MEDIA_TYPES = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'],
        'video' => ['mp4', 'avi', 'mov', 'webm', 'mkv', 'flv', 'wmv', 'm4v'],
        'audio' => ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma'],
        'pdf' => ['pdf'],
        'document' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
        'archive' => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'],
    ];
    public static $priority = 101;

    public function addOptions()
    {
        return [
            'HTMLAttributes' => [
                'target' => '_blank',
                'rel' => 'noopener noreferrer nofollow',
                'class' => 'media-picker-link',
            ],
            'detectMediaType' => true,
            'addMediaTypeClass' => true,
        ];
    }

    public function addAttributes()
    {
        return [
            'href' => [],
            'target' => [],
            'title' => [],
            'class' => [],
            'data-media-type' => [],
            'data-media-id' => [],
        ];
    }


    /**
     * Detect media type from URL
     */
    public function detectMediaType($url)
    {
        if (!$url) {
            return 'generic';
        }

        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        foreach (self::MEDIA_TYPES as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return 'generic';
    }

    /**
     * Generate CSS classes based on media type
     */
    public function generateMediaClasses($url, $existingClass = '')
    {
        $classes = ['media-picker-link'];
        
        if ($existingClass) {
            $classes[] = $existingClass;
        }

        if ($this->options['detectMediaType'] && $this->options['addMediaTypeClass']) {
            $mediaType = $this->detectMediaType($url);
            $classes[] = "media-{$mediaType}";
        }

        return implode(' ', array_unique($classes));
    }

    /**
     * Generate TipTap node structure for a media link
     */
    public function createMediaLinkNode($url, $text = null, $options = [])
    {
        $mediaType = $this->detectMediaType($url);
        
        return [
            'type' => 'text',
            'text' => $text ?: ($options['title'] ?? 'Media Link'),
            'marks' => [
                [
                    'type' => 'link',
                    'attrs' => [
                        'href' => $url,
                        'target' => $options['target'] ?? '_blank',
                        'title' => $options['title'] ?? null,
                        'class' => $this->generateMediaClasses($url, $options['class'] ?? ''),
                        'data-media-type' => $mediaType,
                    ],
                ],
            ],
        ];
    }

    /**
     * Helper method to create multiple media links
     */
    public function createMediaLinkList(array $mediaItems)
    {
        $nodes = [];
        
        foreach ($mediaItems as $index => $item) {
            // Add line break between items (except for the first one)
            if ($index > 0) {
                $nodes[] = [
                    'type' => 'hardBreak',
                ];
            }
            
            $url = $item['url'] ?? $item['src'] ?? $item['href'] ?? '';
            $text = $item['title'] ?? $item['name'] ?? $item['alt'] ?? 'Media Link';
            $options = [
                'title' => $item['description'] ?? $item['title'] ?? null,
                'target' => $item['target'] ?? '_blank',
                'class' => $item['class'] ?? '',
            ];
            
            if ($url) {
                $nodes[] = $this->createMediaLinkNode($url, $text, $options);
            }
        }
        
        return $nodes;
    }

    /**
     * Create media picker content for insertion into TipTap editor
     */
    public function createMediaPickerContent(array $mediaItems, $wrapInParagraph = true)
    {
        $content = $this->createMediaLinkList($mediaItems);
        
        if ($wrapInParagraph && !empty($content)) {
            return [
                [
                    'type' => 'paragraph',
                    'content' => $content,
                ],
            ];
        }
        
        return $content;
    }
}
