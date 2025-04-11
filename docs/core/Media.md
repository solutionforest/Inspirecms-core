# Media Management

InspireCMS provides a comprehensive media management system for handling images, documents, videos, and other files. This guide explains how to upload, organize, and use media in your content.

## Media System Overview

The media system in InspireCMS allows you to:

- Upload various file types (images, documents, videos, etc.)
- Organize files in folders and collections
- Search and filter media assets
- Add metadata to media files
- Insert media into content
- Generate thumbnails and responsive images
- Manage permissions for media access

## Media Library

### Accessing the Media Library

The media library is accessible from:

```
Admin Panel → Media → Library
```

### Browsing Media

The media library interface includes:

- **Grid/List Views**: Toggle between visual grid and detailed list
- **Folders**: Organize media in a hierarchical structure
- **Search**: Find media by filename, type, or metadata
- **Filters**: Filter by date, file type, or custom attributes
- **Sorting**: Arrange files by name, date, size, or type

### File Details

Click on a file to view detailed information:

- **Preview**: Visual preview (when applicable)
- **Metadata**: File information and custom metadata
- **Usage**: Where the file is being used
- **Properties**: Technical information (dimensions, format, size)
- **Actions**: Download, edit, move, or delete

## Uploading Files

### Upload Methods

InspireCMS supports multiple upload methods:

1. **Drag and Drop**: Drag files directly into the media library
2. **File Browser**: Click "Upload" and select files from your computer
3. **URL Import**: Import files from external URLs
4. **Bulk Upload**: Upload multiple files simultaneously

### Upload Configuration

Configure upload settings in `config/inspirecms.php`:

```php
'media' => [
    'user_avatar' => [
        'disk' => 'public',
        'directory' => 'avatars',
    ],
    'media_library' => [
        'disk' => 'public',
        'directory' => '',
        'thumbnail' => [
            'width' => 300,
            'height' => 300,
        ],
        'should_map_video_properties_with_ffmpeg' => false,
        'middlewares' => [
            'cache.headers:public;max_age=2628000;etag',
        ],
    ],
],
```

## File Organization

### Folder Structure

Organize your media with folders:

1. Click "Create Folder" in the media library
2. Name your folder
3. Optionally, choose a parent folder
4. Click "Create"

### Moving Files

To move files between folders:

1. Select the file(s) you want to move
2. Click "Move" or drag them to the destination folder
3. Confirm the move operation

### Bulk Operations

Perform actions on multiple files:

1. Select files by checking their boxes
2. Use the actions bar to:
   - Move selected files
   - Apply metadata to selected files
   - Download selected files
   - Delete selected files

## Media Usage

### Inserting Media into Content

To add media to your content:

1. Edit your content
2. Place cursor where you want to insert media
3. Click the "Media" button in the editor toolbar
4. Select the file from the media picker
5. Configure display options (size, alignment, etc.)
6. Insert the media

### Media Fields

Content types can include dedicated media fields:

```php
// In a filament form schema definition
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\MediaPicker;

MediaPicker::make('hero_image')
    ->label('Hero Image')
    ->filterTypes(['image'])
    ->min(1)
    ->max(1)
```

In templates, access media fields:

```php
 @propertyArray('hero', 'image_slider')
 @foreach ($hero_image_slider ?? [] as $item)
     <div class="swiper-slide">
         <img src="{{ $item?->getUrl() }}" alt="Slide {{ $loop->iteration }}">
         <p>{{ $item?->description }}</p>
     </div>
 @endforeach
```

### Media in Templates

Access media directly in templates:

```php
@php
    $image = inspirecms_asset()->findByKey('550e8400-e29b-41d4-a716-446655440000');
@endphp

@if($image)
    <img src="{{ $image->getUrl() }}" alt="{{ $image->description }}">
@endif
```

### Responsive Images

Generate responsive image variants:

```php
@propertyArray('hero', 'image')
@if(!empty($hero_image))
    <img 
        src="{{ $hero_image[0]->getUrl() }}" 
        srcset="{{ $hero_image[0]->getSrcset(['small', 'medium', 'large']) }}"
        sizes="(max-width: 768px) 100vw, 50vw"
        alt="{{ $hero_image[0]->description }}"
    >
@endif
```

## Image Processing

### Automatic Image Optimization

InspireCMS can automatically optimize images on upload:

```php
// config/inspirecms.php
'media' => [
    'image_optimization' => [
        'enabled' => true,
        'quality' => 85,
        'convert_to_webp' => true,
    ],
],
```

### Image Transformations

Apply transformations to images:

```php
$image = inspirecms_asset()->findByKey('550e8400-e29b-41d4-a716-446655440000');

// Get a resized version
$thumbnail = $image->getUrl(['width' => 300, 'height' => 200, 'fit' => 'crop']);

// Get a filtered version
$grayscale = $image->getUrl(['filter' => 'greyscale']);
```

## Media Metadata

### Default Metadata

Every media file includes standard metadata:

- Filename
- File type and extension
- File size
- Upload date
- Uploader
- Dimensions (for images)
- Duration (for audio/video)

### Custom Metadata

Add custom metadata to media files:

1. Select a file in the media library
2. Click "Edit"
3. Add metadata fields:
   - **Title**: Display name for the media
   - **Alt Text**: Alternative text for accessibility
   - **Caption**: Explanatory text shown with the media
   - **Description**: Longer description for internal use
   - **Custom Fields**: Additional metadata fields

### Metadata in Templates

Use metadata in your templates:

```php
@propertyArray('gallery', 'images')
@foreach($gallery_images ?? [] as $image)
    <figure>
        <img src="{{ $image->getUrl() }}" alt="{{ $image->alt_text }}">
        <figcaption>{{ $image->caption }}</figcaption>
    </figure>
@endforeach
```

## Media Storage

### Storage Configuration

Configure where media is stored:

```php
// config/filesystems.php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    ],
],
```

Link your storage:

```bash
php artisan storage:link
```

### Changing Storage Disk

To use a different storage provider:

```php
// config/inspirecms.php
'media' => [
    'media_library' => [
        'disk' => 's3',
        'directory' => 'media',
        // other settings...
    ],
],
```

## Media Security

### Permission Control

Control who can access the media library:

```php
// In your AuthServiceProvider
use Illuminate\Support\Facades\Gate;
use SolutionForest\InspireCms\Models\MediaAsset;

public function boot()
{
    Gate::define('viewMediaLibrary', function ($user) {
        return $user->hasPermissionTo('view_media');
    });
    
    Gate::define('uploadMedia', function ($user) {
        return $user->hasPermissionTo('upload_media');
    });
    
    Gate::define('deleteMedia', function ($user) {
        return $user->hasPermissionTo('delete_media');
    });
}
```

### Private Media

For sensitive or restricted media:

```php
// config/inspirecms.php
'media' => [
    'private_library' => [
        'enabled' => true,
        'disk' => 'local', // Non-public disk
        'directory' => 'private-media',
        'middleware' => [
            'auth', // Require authentication
            'can:view-private-media', // Check permission
        ],
    ],
],
```

Access private media:

```php
// In a controller
$file = inspirecms_asset()->findByKey('550e8400-e29b-41d4-a716-446655440000');

if ($request->user()->can('view', $file)) {
    return response()->file(storage_path('app/private-media/' . $file->path));
}

return abort(403);
```

## Advanced Features

### EXIF Data

Extract and preserve EXIF data from images:

```php
// config/inspirecms.php
'media' => [
    'preserve_exif' => [
        'enabled' => true,
        'properties' => ['Camera', 'Aperture', 'FocalLength', 'ShutterSpeed'],
    ],
],
```

### Video Processing

Process uploaded videos (requires FFmpeg):

```php
// config/inspirecms.php
'media' => [
    'media_library' => [
        'should_map_video_properties_with_ffmpeg' => true,
        'video_thumbnails' => [
            'enabled' => true,
            'time_offset' => 3, // seconds from start
            'width' => 640,
            'height' => 360,
        ],
    ],
],
```

### SVG Support

Configure SVG support with safety measures:

```php
// config/inspirecms.php
'media' => [
    'svg_support' => [
        'enabled' => true,
        'sanitize' => true, // Clean potentially harmful content
    ],
],
```

## Media Usage Tracking

InspireCMS tracks where media is being used:

1. View a file in the media library
2. See the "Usage" tab for a list of content using this media
3. Exercise caution when deleting files in use

## Best Practices

- **Organize Logically**: Use a consistent folder structure
- **Meaningful Filenames**: Use descriptive, URL-friendly filenames
- **Complete Metadata**: Add alt text and descriptions for accessibility
- **Optimize Images**: Use appropriate file formats and compression
- **Regular Cleanup**: Remove unused files periodically
- **Backup Strategy**: Include media in your backup routine
- **Responsive Images**: Use responsive techniques for different screen sizes
- **Accessibility**: Ensure all media has appropriate alt text