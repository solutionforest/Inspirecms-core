---
title: Content
slug: fe-content
path: docs/v1/fe-content
uri: /docs/1.x/fe-content
heading: Assets
brief: Learn how to work with assets in your frontend templates.
---

## Overview

For detailed media configuration options, please refer to the [configuration documentation](./configuration#media-management){.doc-link}.

---

## Asset Helper

Use the `inspirecms_asset()` helper to work with media assets:

```php
// Get asset by ID
$asset = inspirecms_asset()->findByKeys('550e8400-e29b-41d4-a716-446655440000')?->first();

// Get multiple assets
$assets = inspirecms_asset()->findByKeys('550e8400-e29b-41d4-a716-446655440000', '7f1b96c0-d4f0-11ed-afa1-0242ac120002');
```

---

## Accessing Asset Properties

Access basic asset information:

```php
// Get asset properties
$url = $asset->getUrl();            // Full URL to the asset
$filename = $asset->getFilename();  // Original filename
$extension = $asset->getExtension(); // File extension
$size = $asset->getSize();          // File size in bytes
$mimeType = $asset->getMimeType();  // MIME type

// Get asset metadata
$title = $asset->title;             // Title
$caption = $asset->caption;         // Caption
$description = $asset->description; // Description
```

---

### Image Transformations

Create responsive image variants:

Predefined variants (from config `media.media_library.responsive_images`):

-   **small**: width 400 px
-   **medium**: width 600 px

```php
// Get default URL
$url = $asset->getUrl();

// Get predefined responsive variants
$smallUrl  = $asset->getUrl('small');
$mediumUrl = $asset->getUrl('medium');
```

---

## Working with Assets in Templates

### Accessing Media from Properties

Use property directives to access media assets linked to content:

```blade
<!-- Single image -->
@property('hero', 'image')
@if($hero_image)
    <img src="{{ $hero_image->getUrl() }}" alt="{{ $hero_image->caption }}">
@endif

<!-- Multiple images -->
@propertyArray('gallery', 'images')
<div class="gallery">
    @foreach($gallery_images as $image)
        <figure>
            <img
                src="{{ $image->getUrl() }}"
                alt="{{ $image->caption }}"
                srcset="{{ $image->getSrcset(['small', 'medium']) }}"
                loading="lazy"
            >
            @if($image->caption)
                <figcaption>{{ $image->caption }}</figcaption>
            @endif
        </figure>
    @endforeach
</div>
```

### Responsive Images

Implement responsive images:

```blade
<!-- Basic responsive image -->
<img
    src="{{ $asset->getUrl() }}"
    srcset="{{ $asset->getSrcset(['small', 'medium']) }}"
    sizes="(max-width: 768px) 100vw, 50vw"
    alt="{{ $asset->caption }}"
>
```
