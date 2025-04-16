# Import

InspireCMS provides import capabilities for migrating content and system configurations. This guide explains how to use the import features effectively.

## Import System Overview

The import system in InspireCMS allows you to import:

- Content items
- Document types
- Field groups
- Navigation menus
- Templates
- Views and components

## Supported Format

InspireCMS currently supports importing data through a ZIP archive with a specific structure:

```
archive.zip/
├── Content/
│   ├── content-1.json
│   └── content-2.json
├── DocumentTypes/
│   ├── document-types-1.json
│   └── document-types-2.json
├── FieldGroups/
│   ├── field-group-1.json
│   └── field-group-2.json
├── NavigationMenus/
│   ├── navigation-menu-1.json
│   └── navigation-menu-2.json
├── Templates/
│   ├── template-1/
│   │   ├── theme-1.json
│   │   └── theme-2.json
│   └── template-2/
│       ├── theme-1.json
│       └── theme-2.json
└── Views/
    ├── components/
    │   ├── component-1.blade.php
    │   └── component-2.blade.php
    ├── sample-1.blade.php
    └── sample-2.blade.php
```

### File Structure Requirements

1. **Content Directory**
   - Contains JSON files defining content items
   - Each file represents one or more content entries

2. **DocumentTypes Directory**
   - Contains JSON files defining document types
   - Each file can contain multiple document type definitions

3. **FieldGroups Directory**
   - Contains JSON files defining field groups
   - Each file can contain one or more field group definitions

4. **Navigation Directory**
   - Contains JSON files defining navigation menus
   - Each file represents a navigation menu structure

5. **Templates Directory**
   - Contains subdirectories for each template
   - Each template directory contains theme-specific JSON files

6. **Views Directory**
   - Contains Blade template files
   - `components` subdirectory for component views
   - Root level for main template files

## Creating an Import Package

1. Create the directory structure as shown above
2. Add your JSON files in the appropriate directories
3. Add your Blade template files in the Views directory
4. Compress the directories into a ZIP file
5. Upload through the admin interface

### Example JSON Structures

**Content (content-1.json)**:

```json
{
    "title": {"en": "Sample Page"},
    "slug": "sample-page",
    "document_type": "page",
    "parent": "home",
    "properties": {
        "content": {
            "body": {"en": "<p>Sample content</p>"}
        }
    },
    "publishState": "publish",
    "sitemap": {
        "change_frequency": "monthly",
        "priority": 0.5,
        "enable": true
    },
    "webSetting": {
        "seo": {
            "meta_keywords": [],
            "og_image": [],
            "meta_title": {
                "en": "Sample Page"
            },
            "meta_description": {
                "en": null
            },
            "og_title": {
                "en": null
            },
            "og_description": {
                "en": null
            }
        },
        "robots": {
            "noindex": false,
            "nofollow": false
        },
        "redirect_path": null,
        "redirect_content_id": "00000000-0000-0000-0000-000000000000",
        "redirect_type": null
    },
    "template": null
}
```

**Document Type (document-types-1.json)**:

```json
{
    "slug": "page",
    "title": "Page",
    "showAsTable": false,
    "showAtRoot": false,
    "category": "web",
    "icon": "heroicon-c-document",
    "fieldGroups": ["content"],
    "templates": ["default"],
    "defaultTemplate": "default",
    "allowed": []
}
```

**FieldGroups (field-group-1.json)**:

```json
{
    "slug": "content",
    "title": "Content",
    "fields": [
        {
            "slug": "body",
            "label": "Body",
            "type": "markdownEditor",
            "config": {
                "translatable": true,
                "toolbarButtons": [
                    "attachFiles",
                    "blockquote",
                    "bold",
                    "bulletList",
                    "codeBlock",
                    "h2",
                    "h3",
                    "italic",
                    "link",
                    "orderedList",
                    "redo",
                    "strike",
                    "underline",
                    "undo"
                ],
                "fileAttachmentsDisk": "public",
                "fileAttachmentsDirectory": null,
                "fileAttachmentsVisibility": null
            }
        }
    ]
}
```

**NavigationMenus (navigation-menu-1.json)**

```json
{
    "id": 1,
    "category": "topbar",
    "type": "group",
    "title": {
        "en": "Home"
    },
    "contentSlugPath": null,
    "url": {
        "en": null
    },
    "target": null,
    "children": [
        {
            "id": 2,
            "category": "topbar",
            "type": "content",
            "title": {
                "en": "Sample page"
            },
            "contentSlugPath": "home/sample-page",
            "url": {
                "en": null
            },
            "target": null,
            "children": []
        },
        {
            "id": 3,
            "category": "topbar",
            "type": "content",
            "title": {
                "en": "URL"
            },
            "contentSlugPath": null,
            "url": {
                "en": "#jumpHere"
            },
            "target": null,
            "children": []
        }
    ]
}
```

**Templates (template-1/theme-1.blade.php)**

```php
<x-cms-template :content="$content" type="page" class="sample-class">
    @property('content', 'body')
</x-cms-template>
```

**Views (components/inspirecms/theme-1/page.blade.php)**

```php
@php
    $locale ??= $content->getLocale() ?? request()->getLocale();
@endphp
<x-dynamic-component :component="inspirecms_templates()->getComponentWithTheme('layout')" :title="$content->getTitle()" :seo="$content->getSeo()?->getHtml()" :locale="$locale">

    <main class="flex-1 overflow-y-auto">
        
        <x-dynamic-component :component="inspirecms_templates()->getComponentWithTheme('topbar')" :locale="$locale" />

        <!-- Main Content Area -->
        <div {{ $attributes->merge(['class' => 'lg:pr-8']) }}>
            {{ $slot }}
        </div>
    </main>

</x-dynamic-component>
```

**Views (components/inspirecms/theme-1/layout.blade.php)**

```php
@php
    $title ??= config('app.name');
    $locale ??= request()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        @if (isset($seo) && $seo instanceof \Illuminate\Contracts\Support\Htmlable)
            {{ $seo }}
        @endif
        @yield('styles')
    </head>
    <body>
        {{ $slot }}
        @yield('scripts')
    </body>
</html>
```

**Views (components/inspirecms/theme-1/topbar.blade.php)**

```php
@php
    $locale ??= request()->getLocale();
@endphp

<!-- top nav -->
<nav class="top-main-nav">
    @foreach (inspirecms()->getNavigation('topbar', $locale) as $item)
        <div class="nav-section">
            @if ($item->hasChildren())
                <h3 class="has-dropdown">$item->getTitle()</h3>
                <ul class="submenu">
                    @foreach ($item->children as $child)
                        <li><a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a></li>
                    @endforeach
                </ul>
            @else
                <div>
                    <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
                </div>
            @endif
        </div>
    @endforeach
</nav>
```