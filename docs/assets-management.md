---
title: Assets Management
slug: assets-management
path: docs/v1/assets-management
uri: /docs/1.x/assets-management
---
# Assets Management

Learn how to manage media assets in InspireCMS.

---

## Media Library

The media library provides a centralized system for storing and managing your images, documents, videos, and other files.

### Configuration

For detailed media configuration options, please refer to the [Media Management](./configuration#media-management){.doc-link} section in the configuration documentation.

### Asset Helper

Use the `inspirecms_asset()` helper to work with media assets:

```php
// Get asset by ID
$asset = inspirecms_asset()->findByKeys('550e8400-e29b-41d4-a716-446655440000')?->first();

// Get multiple assets
$assets = inspirecms_asset()->findByKeys('550e8400-e29b-41d4-a716-446655440000', '7f1b96c0-d4f0-11ed-afa1-0242ac120002');

```

### Accessing Asset Properties

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

### Image Transformations

Create responsive image variants:

```php
// Get default URL
$url = $asset->getUrl();

// Get predefined responsive variants
$smallUrl = $asset->getUrl('small');
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
                src="{{ $image->getUrl(['width' => 800]) }}" 
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
    srcset="{{ $asset->getSrcset(['small', 'medium', 'large']) }}"
    sizes="(max-width: 768px) 100vw, 50vw"
    alt="{{ $asset->caption }}"
>

<!-- Picture element for art direction -->
<picture>
    <source media="(max-width: 768px)" srcset="{{ $asset->getUrl(['width' => 600, 'height' => 400, 'fit' => 'crop']) }}">
    <source media="(max-width: 1200px)" srcset="{{ $asset->getUrl(['width' => 1200, 'height' => 600, 'fit' => 'crop']) }}">
    <img src="{{ $asset->getUrl() }}" alt="{{ $asset->caption }}">
</picture>
```

### Lazy Loading

Implement lazy loading for better performance:

```blade
<img 
    src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" 
    data-src="{{ $asset->getUrl() }}"
    class="lazy"
    alt="{{ $asset->caption }}"
>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));
        
        if ("IntersectionObserver" in window) {
            let lazyImageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.classList.remove("lazy");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });
            
            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        }
    });
</script>
```

### Video and Document Handling

Handle different file types:

```blade
@propertyArray('content', 'media')
@foreach ($content_media ?? [] as $media)
    @if($media)
        @php $mimeType = $media->getMimeType(); @endphp
        
        @if(Str::startsWith($mimeType, 'image/'))
            <img src="{{ $media->getUrl() }}" alt="{{ $media->caption }}">
        @elseif(Str::startsWith($mimeType, 'video/'))
            <video controls>
                <source src="{{ $media->getUrl() }}" type="{{ $mimeType }}">
                Your browser does not support video playback.
            </video>
        @elseif($mimeType === 'application/pdf')
            <a href="{{ $media->getUrl() }}" class="btn btn-primary" target="_blank">
                View PDF Document
            </a>
        @else
            <a href="{{ $media->getUrl() }}" class="btn btn-secondary" download>
                Download File ({{ $media->getFilename() }})
            </a>
        @endif
    @endif
@endforeach
```

---

## Best Practices

1. **Optimize Images**: Configure appropriate image sizes and compression
2. **Use Responsive Images**: Implement `srcset` and `sizes` attributes
3. **Implement Lazy Loading**: Defer loading off-screen images
4. **Set Alt Text**: Always provide meaningful alternative text
5. **Consider File Size**: Monitor asset sizes for optimal performance
6. **Use CDN**: Configure a CDN for assets when possible
7. **Cache Assets**: Set appropriate cache headers for static assets
8. **Regular Cleanup**: Remove unused assets periodically