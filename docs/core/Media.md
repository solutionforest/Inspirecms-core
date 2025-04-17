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
Admin Panel → Media 
```

### Browsing Media

The media library interface includes:

- **Grid Views**: Toggle between visual grid list
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
3. **Bulk Upload**: Upload multiple files simultaneously

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
        'allowed_mime_types' => [], // Allowed file types
        'max_file_size' => null, // Maximum file size in KB
        'thumbnail' => [
            'width' => 300,
            'height' => 300,
        ],
        'should_map_video_properties_with_ffmpeg' => false,
        'middlewares' => [
            'cache.headers:public;max_age=2628000;etag',
        ],
        'responsive_images' => [
            'small' => [
                'enabled' => true,
                'width' => 400,
            ],
            'medium' => [
                'enabled' => true,
                'width' => 600,
            ],
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

## Media Usage

### Inserting Media into Content

To add media to your content:

1. Edit your content
2. Place cursor where you want to insert media
3. Click the "Media" button in the editor toolbar
4. Select the file from the media picker
5. Insert the media

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
        srcset="{{ $hero_image[0]->getSrcset(['small', 'medium']) }}"
        sizes="(max-width: 768px) 100vw, 50vw"
        alt="{{ $hero_image[0]->description }}"
    >
@endif
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

### Metadata in Templates

Use metadata in your templates:

```php
@propertyArray('gallery', 'images')
@foreach($gallery_images ?? [] as $image)
    <figure>
        <img src="{{ $image->getUrl() }}" alt="{{ $image->caption }}">
        <figcaption>{{ $image->description }}</figcaption>
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

Control who can access and manage media by registering a custom policy class:

```php
// config/inspirecms.php
return [
    // Other config options...
    
    'models' => [
        'policies' => [
            'media_asset' => \App\Policies\MediaAssetPolicy::class,
        ],
    ],
];
```

Create your custom policy class:

```php
namespace App\Policies;

use SolutionForest\InspireCms\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_media');
    }
    
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('upload_media');
    }
    
    public function delete(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->hasPermissionTo('delete_media');
    }
    
    // Define other permissions as needed
}
```

## Best Practices

- **Organize Logically**: Use a consistent folder structure
- **Meaningful Filenames**: Use descriptive, URL-friendly filenames
- **Complete Metadata**: Add alt text and descriptions for accessibility
- **Optimize Images**: Use appropriate file formats and compression
- **Responsive Images**: Use responsive techniques for different screen sizes
- **Accessibility**: Ensure all media has appropriate alt text