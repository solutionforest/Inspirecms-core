<?php

namespace SolutionForest\InspireCms\Examples;

use SolutionForest\InspireCms\Services\TipTapContentService;
use SolutionForest\InspireCms\MediaPickerLink\MediaPickerLink;
use SolutionForest\InspireCms\ContentPickerLink\ContentPickerLink;

/**
 * Usage examples for TipTap Media Picker and Content Picker PHP Extensions
 * Now located at the root level of the src directory
 */
class TipTapUsageExamples
{
    /**
     * Example: Creating media picker content using the new root-level classes
     */
    public function createMediaPickerExample()
    {
        // Sample media items
        $mediaItems = [
            [
                'url' => 'https://example.com/image.jpg',
                'title' => 'Sample Image',
                'description' => 'A beautiful sample image',
                'alt' => 'Image alt text',
            ],
            [
                'url' => 'https://example.com/video.mp4',
                'title' => 'Sample Video',
                'description' => 'An awesome video',
            ],
            [
                'url' => 'https://example.com/document.pdf',
                'title' => 'Important Document',
                'description' => 'A PDF document',
            ],
        ];

        // Using the service class (recommended)
        $tipTapContent = TipTapContentService::createMediaPickerContent($mediaItems);
        
        // Or using the MediaPickerLink class directly
        $mediaPickerLink = MediaPickerLink::make();
        $alternativeContent = $mediaPickerLink->createMediaPickerContent($mediaItems);

        return [
            'service_method' => $tipTapContent,
            'direct_method' => $alternativeContent,
        ];
    }

    /**
     * Example: Creating content picker content using the new root-level classes
     */
    public function createContentPickerExample()
    {
        // Sample content items
        $contentItems = [
            [
                'url' => '/blog/first-post',
                'title' => 'First Blog Post',
                'description' => 'Our very first blog post',
            ],
            [
                'url' => '/about',
                'title' => 'About Us',
                'description' => 'Learn more about our company',
            ],
            [
                'url' => 'https://external-site.com',
                'title' => 'External Reference',
                'description' => 'Link to external resource',
            ],
        ];

        // Using the service class (recommended)
        $tipTapContent = TipTapContentService::createContentPickerContent($contentItems);
        
        // Or using the ContentPickerLink class directly
        $contentPickerLink = ContentPickerLink::make();
        $alternativeContent = $contentPickerLink->createContentPickerContent($contentItems);

        return [
            'service_method' => $tipTapContent,
            'direct_method' => $alternativeContent,
        ];
    }

    /**
     * Example: Using the moved service class
     */
    public function serviceClassExample()
    {
        $mediaItems = [
            ['url' => 'image.jpg', 'title' => 'Image'],
            ['url' => 'video.mp4', 'title' => 'Video'],
        ];

        $contentItems = [
            ['url' => '/page', 'title' => 'Page'],
            ['url' => '/blog', 'title' => 'Blog'],
        ];

        return [
            'media_links' => TipTapContentService::convertMediaItemsToLinks($mediaItems),
            'content_links' => TipTapContentService::convertContentItemsToLinks($contentItems),
            'media_command' => TipTapContentService::generateMediaInsertCommand($mediaItems),
            'content_command' => TipTapContentService::generateContentInsertCommand($contentItems),
        ];
    }

    /**
     * Example: Direct usage of the root-level classes
     */
    public function directUsageExample()
    {
        // Using MediaPickerLink directly
        $mediaPickerLink = MediaPickerLink::make([
            'detectMediaType' => true,
            'addMediaTypeClass' => true,
        ]);

        $mediaUrl = 'sample.jpg';
        $mediaType = $mediaPickerLink->detectMediaType($mediaUrl);
        $mediaClasses = $mediaPickerLink->generateMediaClasses($mediaUrl);

        // Using ContentPickerLink directly
        $contentPickerLink = ContentPickerLink::make([
            'HTMLAttributes' => [
                'class' => 'custom-content-link',
            ],
        ]);

        $contentNode = $contentPickerLink->createContentLinkNode('/sample-page', 'Sample Page', [
            'title' => 'A sample page',
            'target' => '_self',
        ]);

        return [
            'media_type' => $mediaType,
            'media_classes' => $mediaClasses,
            'content_node' => $contentNode,
        ];
    }

    /**
     * Example: Creating complete TipTap documents
     */
    public function createDocumentExample()
    {
        $mediaItems = [
            ['url' => 'hero.jpg', 'title' => 'Hero Image'],
            ['url' => 'gallery.mp4', 'title' => 'Gallery Video'],
        ];

        $contentItems = [
            ['url' => '/related-article', 'title' => 'Related Article'],
            ['url' => '/external-link', 'title' => 'External Resource'],
        ];

        // Create complete documents using the service
        $mediaDocument = TipTapContentService::processMediaItems($mediaItems);
        $contentDocument = TipTapContentService::processContentItems($contentItems);

        // Or create using the classes directly
        $mediaPickerLink = MediaPickerLink::make();
        $contentPickerLink = ContentPickerLink::make();

        $directMediaDocument = $mediaPickerLink->createMediaDocument($mediaItems);
        $directContentDocument = $contentPickerLink->createContentDocument($contentItems);

        return [
            'service_media_doc' => $mediaDocument,
            'service_content_doc' => $contentDocument,
            'direct_media_doc' => $directMediaDocument,
            'direct_content_doc' => $directContentDocument,
        ];
    }
}
