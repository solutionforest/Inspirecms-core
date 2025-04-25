# Templates

Learn how to create and manage templates in InspireCMS to build flexible, content-driven layouts for your website.

## Template Overview

InspireCMS templates define how your content is displayed to visitors. Each template is associated with a specific document type and can include custom fields, layouts, and components.

## Theme Management

### Creating a Theme
1. Navigate to `/cms/settings/templates` or `Cms > Settings > Templates`
2. Click `Create theme` or `Clone theme`
3. Enter your theme name and submit
4. New theme components will be placed at `resources/views/components/inspirecms/{new_theme}`

### Changing Themes
1. Navigate to `/cms/settings/templates` or `Cms > Settings > Templates`
2. Click `Change theme`
3. Select your desired theme

> [!TIP]
> You can view the current theme by running `php artisan inspirecms:about`

## Template Creation

1. Navigate to `/cms/settings/document-types` or `Cms > Settings > Document Types`
2. Select your target [document type](./references/DocumentType.md)
3. Create or edit an existing template for that document type

Example:

```php
@php
    $locale ??= $content->getLocale();
@endphp
<x-cms-template :content="$content" type="page">
    <p>@property('banner', 'title')</p>
    <p>Your content here</p>
</x-cms-template>
```

## Adding Fields to Template

### Defining Template Fields

When creating or editing a template, you can define fields in the "Fields" section:

1. Navigate to `/cms/settings/document-types` or `Cms > Settings > Document Types`
2. Select your document type
3. Add fields using the form to input all required form group data

### Field Types

For detailed information about available field types and their configuration options, please see the [Custom Fields documentation](./CustomFields.md).

### Using Fields in Templates

> **Note:** For comprehensive documentation on property directives, see the [Content](./Content.md) documentation and [Blade](./Blade.md) documentation.

#### Access Patterns

Fields can be accessed using different patterns depending on your needs:

- **Simple access**: `@property('fieldGroup', 'fieldName')`  
- **Array access**: `@propertyArray('fieldGroup', 'fieldName')`  
- **Conditional access**: `@propertyNotEmpty('fieldGroup', 'fieldName')`  
- **Custom DTO access**: `@property('fieldGroup', 'fieldName', null, $customDTO)`  
- **Custom variable naming**: `@property('fieldGroup', 'fieldName', 'custom_var')`  

## Implementation Examples

Let's assume you've created a theme named "**abc**".

### Approach 1: Using Components

#### Folder Structure

```
resources/views/components/inspirecms/abc/
├── footer.blade.php
├── header.blade.php
├── layout.blade.php
├── page.blade.php
└── simple-page.blade.php
```

#### Component Files

```php
<!-- resources/views/components/inspirecms/abc/layout.blade.php -->
@php
    $title ??= config('app.name');
    $locale ??= request()->getLocale();
@endphp
<html lang="{{ $locale }}">
    <head>
        @if (isset($seo) && $seo instanceof \Illuminate\Contracts\Support\Htmlable)
            {{ $seo }}
        @else
            <title>{{ $title }}</title>
        @endif
        @yield('styles')
    </head>
    <body>
        {{ $slot }}
        @yield('scripts')
    </body>
</html>
```

> **Note:** For more detailed examples of layout components, see the [Layouts](./Layouts.md) documentation.
> 
> For comprehensive coverage of component architecture, see the [Components](./Components.md) documentation.

### Approach 2: Using Template Inheritance

#### Folder Structure
```
resources/views/
├── layouts/
│   └── inspirecms/
│       └── abc/
│           ├── base.blade.php
│           ├── footer.blade.php
│           └── topnav.blade.php
└── components/
    └── inspirecms/
        └── abc/
            ├── page.blade.php
            └── simple-page.blade.php
```

> **Note:** For detailed information about template inheritance patterns, see the [Layouts](./Layouts.md) documentation.

## Dynamic Templates

Create templates that adapt based on content properties:

```php
<x-cms-template :content="$content" type="page">
    @php
        $layout = $content->getPropertyValue('settings', 'layout', 'standard');
        $hasSidebar = $content->getPropertyValue('settings', 'show_sidebar', false);
    @endphp
    
    <div class="container layout-{{ $layout }}">
        <h1>@property('hero', 'title')</h1>
        
        @if($hasSidebar)
            <div class="row">
                <div class="col-md-8">
                    @property('content', 'body')
                </div>
                <div class="col-md-4">
                    <!-- Sidebar content -->
                    @property('sidebar', 'content')
                </div>
            </div>
        @else
            <div class="full-width">
                @property('content', 'body')
            </div>
        @endif
    </div>
</x-cms-template>
```

## Template Helper Functions

```php
// Get current theme
$theme = inspirecms_templates()->getCurrentTheme();

// Check if a component exists
$exists = inspirecms_templates()->hasComponent('header', 'your-theme');

// Get a component name respecting theme hierarchy
$component = inspirecms_templates()->getComponentWithTheme('header');

// Get theme path
$path = inspirecms_templates()->getPath();
```

## Best Practices

1. **Modular Design**: Break templates into reusable components
2. **Theme Inheritance**: Use theme inheritance for consistent UI
3. **Conditional Logic**: Add conditions to handle missing content
4. **Responsive Design**: Ensure templates work on all devices
5. **Documentation**: Document template fields and their purpose

## Troubleshooting Common Issues

### Component Not Found

If you see a "Component not found" error:

1. Verify the component exists in the correct location
2. Check that the component name is correctly spelled
3. Make sure the theme name matches exactly
4. Try clearing the view cache: `php artisan view:clear`

### Template Not Showing Content

If content isn't displaying:

1. Verify content exists and has values
2. Check property names and field groups match
3. Add conditionals to handle missing content
4. Debug with temporary output of variable values