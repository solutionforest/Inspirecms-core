# Navigation

The Navigation system in InspireCMS provides a flexible way to manage site menus and navigation structures. It allows you to create, organize, and display navigation elements throughout your application.

## Creating Navigation Menus

Navigate to `/cms/settings/navigation` or `Cms > Settings > Navigation` to create and manage navigation menus visually.

## Configuration

Navigation settings can be configured in `config/inspirecms.php`:

```php
return [
    // ...
    'cache' => [
        // ...
        'navigation' => [
            'key' => 'inspirecms.navigation',
            'ttl' => 60 * 60 * 24,
        ],
        // ...
    ],
    // ...
];
```

## Navigation Item Types
- Content  
A `content` type navigation item links to a specific content resource.

- Link  
A `link` type navigation item points to an external or internal URL.

- Group  
A `group` type navigation item organizes multiple child items under a single parent.

## Using Categories
Navigation items can be grouped by categories. To retrieve navigation items for a specific category, use the `getNavigation` method and pass the category name as the first argument:

```php
$nav = inspirecms()->getNavigation('sidebar', $locale);
```

In this example, `sidebar` is the category name. You can define and manage categories in the CMS settings.

## Advanced Features

- **Nested Menus**: Support for multi-level dropdown menus
- **Dynamic Menu Items**: Generate menu items from database content

## Example: Sidebar Navigation

Here is an example of rendering a navigation menu in a Blade template:

```php
@php
    $nav = inspirecms()->getNavigation('sidebar', $locale);
@endphp
<nav class="nav-menu">
    @foreach ($nav as $item)
        <div class="nav-section">
            @if ($item->hasChildren())
                <h3 class="has-dropdown">{{ $item->getTitle() }}</h3>
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