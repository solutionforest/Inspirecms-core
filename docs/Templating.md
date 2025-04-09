# Templating

InspireCMS templates allow you to define reusable layouts and components for your content.

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
2. Select your target document type[^1]
3. Create or edit an existing template for that document type[^1]

InspireCMS uses [Blade](https://laravel.com/docs/11.x/blade) and automatically binds `$content` and `$locale`. 

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

InspireCMS allows you to add custom fields to your templates that content editors can use to populate pages.

### Defining Template Fields

When creating or editing a template, you can define fields in the "Fields" section:

1. Navigate to `/cms/settings/document-types` or `Cms > Settings > Document Types`
2. Select your document type[^1]
3. Add fields using the form to input all required form group data, e.g.
    - Define field name, label, type, and validation rules
    - Organize fields into logical groups if needed
    - Set default values and configuration options
    - etc.

### Field Types

For detailed information about available field types and their configuration options, please see the [Custom Fields documentation](./docs/CustomFields.md).

### Using Fields in Templates

InspireCMS provides several directives to access your field data:
- `@property()` - For basic field access
- `@propertyArray()` - For accessing array fields (repeaters, content pickers, etc.)
- `@propertyNotEmpty()` - For conditional rendering based on field content

#### Access Patterns

Fields can be accessed using different patterns depending on your needs:

- **Simple access**: `@property('fieldGroup', 'fieldName')`  
    Returns the field value directly from `$content` and automatically names the variable `$fieldGroup_fieldName`.

- **Array access**: `@propertyArray('fieldGroup', 'fieldName')`  
    Returns the field value as a PHP array that you can manipulate.

- **Conditional access**: `@propertyNotEmpty('fieldGroup', 'fieldName')`  
    Creates a conditional block with an auto-generated variable containing the field value.

- **Custom DTO access**: `@property('fieldGroup', 'fieldName', null, $customDTO)`  
    Gets the property value from a custom DTO object instead of the default `$content`.

- **Custom variable naming**: `@property('fieldGroup', 'fieldName', 'custom_var')`  
    Retrieves the property value but assigns it to a variable named `$custom_var` instead of the default naming pattern.

- **Combined custom naming and DTO**: `@property('fieldGroup', 'fieldName', 'custom_var', $customDTO)`  
    Gets the property from a custom DTO and assigns it to a variable with a custom name.

```php
<!-- Examples -->
@property('hero', 'title') 
<!-- Value is from $content, variable available as $hero_title -->

@property('hero', 'image', 'hero_img')
<!-- Value is from $content, variable available as $hero_img -->

@property('blog', 'category', null, $blogDTO)
<!-- Value is from $blogDTO, variable available as $blog_category -->

@property('blog', 'author', 'post_writer', $blogDTO)
<!-- Value is from $blogDTO, variable available as $post_writer -->
```


#### Field Type-Specific Notes

Different field types return different data structures:
- **Repeater**: Return arrays that can be iterated with `@foreach` directive
- **Content Picker**: Return array of objects with methods like `getUrl()` and `getTitle()`
- **File/Image**: Return associative arrays with metadata like 'disk', 'path', 'directory', etc.
- **Rich Text**: Return rendered HTML content

> [!NOTE] 
> For detailed information about specific field types and their access patterns, please refer to the [Custom Fields documentation](./docs/CustomFields.md).

#### Example

1. Basic Field Access

```php
<h1>@property('hero', 'title')</h1>

<p>@property('hero', 'subtitle')</p>

@propertyArray('document_content', 'categories')
<!-- Loop through array items -->
@foreach ($blog_content_categories ?? [] as $category)
    <div class="categories">
        <span class="category">{{ $category }}</span>
    </div>
@endforeach
```

2. Conditional Field Access
```php
<!-- Check if field exists or has data -->
@propertyNotEmpty('hero', 'image')
    <img src="{{ \Arr::first($hero_image) }}" alt="@property('hero', 'image_alt')">
@endif

<!-- Alternative syntax -->
@propertyNotEmpty('document_content', 'sections')
    <!-- $document_content_sections is automatically available here -->
    @foreach ($document_content_sections as $section)
        <div class="section">{{ $section->getPropertyData('title')?->getValue() }}</div>
    @endforeach
@endif
```

3. Building a Page with Sections

```php
<x-cms-template :content="$content" type="page">
    <header class="page-header">
        <h1>@property('page_basic', 'title')</h1>
        @propertyNotEmpty('page_basic', 'subtitle')
            <p class="subtitle">{{ $page_basic_subtitle }}</p>
        @endif
    </header>
    
    <section class="intro">
        <div class="container">
            @property('intro', 'text')
        </div>
    </section>
    
    @propertyNotEmpty('content_blocks', 'blocks')
    @foreach ($content_blocks_blocks as $block)
        @php
            $extraBlockStyle = $block->getPropertyData('style')?->getValue();
        @endphp
        <section class="content-block {{ $extraBlockStyle }}">
            <div class="container">
                <h2>{{ $block->getPropertyData('heading')?->getValue() }}</h2>
                <div class="content">
                    {{ $block->getPropertyData('content')?->getValue() }}
                </div>
                @if($block->getPropertyData('has_button')?->getValue() ?? false)
                    <a href="{{ $block->getPropertyData('button_link')?->getValue() }}" class="btn">
                        {{ $block->getPropertyData('button_text')?->getValue() }}
                    </a>
                @endif
            </div>
        </section>
    @endforeach
    @endif
</x-cms-template>
```

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

```php
<!-- resources/views/components/inspirecms/abc/header.blade.php -->
<nav>
    @foreach (inspirecms()->getNavigation('main', $locale ?? request()->getLocale()) as $item)
        <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
    @endforeach
</nav>
```

```php
<!-- resources/views/components/inspirecms/abc/footer.blade.php -->
<footer>
    <div>
        @foreach (inspirecms()->getNavigation('footer', $locale ?? request()->getLocale()) as $item)
            <div>
                <h4>{{ $item->getTitle() }}</h4>
                @if ($item->hasChildren())
                    <ul>
                        @foreach ($item->children as $child)
                            <li><a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <div class="copyright">
        <p>Copyright</p>
    </div>
</footer>
```

```php
<!-- resources/views/components/inspirecms/abc/page.blade.php -->
@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();

    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
    $headerComponent = inspirecms_templates()->getComponentWithTheme('header');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
@endphp
<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale">
    <x-dynamic-component :component="$headerComponent" :locale="$locale" />
    {{ $slot }}
    <x-dynamic-component :component="$footerComponent" :locale="$locale" />
</x-dynamic-component>
```

```php
<!-- resources/views/components/inspirecms/abc/simple-page.blade.php -->
@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();

    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
@endphp
<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale">
    {{ $slot }}
    <x-dynamic-component :component="$footerComponent" :locale="$locale" />
</x-dynamic-component>
```

#### Applying Layouts to Templates

```php
<!-- Template: home -->
<x-cms-template :content="$content" type="page">
    Home
</x-cms-template>
```

```php
<!-- Template: tnc -->
<x-cms-template :content="$content" type="simple-page">
    TNC Here
</x-cms-template>
```

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

Learn more about [layouts using inheritance in Blade](https://laravel.com/docs/11.x/blade#layouts-using-template-inheritance).

#### Template Files

```php
<!-- resources/views/layouts/inspirecms/abc/base.blade.php -->
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">
        @hasSection('seo')
            @yield('seo')
        @endif
        @sectionMissing('seo')
            <title>App Name - @yield('title')</title>
        @endif
    </head>
    <body>
        @yield('content')
    </body>
</html>
```

```php
<!-- resources/views/layouts/inspirecms/abc/topnav.blade.php -->
<nav>
    @foreach (inspirecms()->getNavigation('topnav', $locale ?? request()->getLocale()) as $item)
        <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
    @endforeach
</nav>
```

**Footer Layout**
```php
<!-- resources/views/layouts/inspirecms/abc/footer.blade.php -->
<footer>
    <div>
        @foreach (inspirecms()->getNavigation('footer', $locale ?? request()->getLocale()) as $item)
            <div>
                <h4>{{ $item->getTitle() }}</h4>
                @if ($item->hasChildren())
                    <ul>
                        @foreach ($item->children as $child)
                            <li><a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <div class="copyright">
        <p>Copyright</p>
    </div>
</footer>
```

```php
<!-- resources/views/components/inspirecms/abc/page.blade.php -->
@extends('layouts.'.inspirecms_templates()->getComponentWithTheme('base'))

@section('content')
    @include('layouts.'.inspirecms_templates()->getComponentWithTheme('topnav'))
    <div class="container">
        @yield('page-content', 'No content found')
    </div>
    @include('layouts.'.inspirecms_templates()->getComponentWithTheme('footer'))
@endsection
```

```php
<!-- resources/views/components/inspirecms/abc/simple-page.blade.php -->
@extends('layouts.'.inspirecms_templates()->getComponentWithTheme('base'))

@section('content')
    <div class="container">
        @yield('page-content', 'No content found')
    </div>
    @include('layouts.'.inspirecms_templates()->getComponentWithTheme('footer'))
@endsection
```

#### Applying Layouts to Templates

```php
<!-- Template: home -->
@extends('components.'.inspirecms_templates()->getComponentWithTheme('page'))
@section('seo', $content->getSeo()?->getHtml())
@section('title', $content->getTitle())
@section('page-content')
    <p>This is my body content.</p>
@endsection
```

```php
<!-- Template: tnc -->
@extends('components.'.inspirecms_templates()->getComponentWithTheme('simple-page'))
@section('seo', $content->getSeo()?->getHtml())
@section('title', $content->getTitle())
@section('page-content')
    <p>TNC</p>
@endsection
```

[^1]: Document types define the structure and behavior of content in InspireCMS. For detailed information, see the [Document Type](../docs/docs/references/DocumentType.md).
