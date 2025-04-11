# Themes

InspireCMS offers a powerful theming system that allows you to customize the look and feel of your website. This guide covers theme creation, management, and customization.

## Theme System Overview

Themes in InspireCMS are organized collections of templates, components, assets, and configurations that determine how your website looks and behaves. The theme system:

- Separates content from presentation
- Allows for easy switching between different designs
- Enables component reuse across templates
- Supports theme inheritance and overriding

## Theme Structure

A theme in InspireCMS consists of the following components:

```
resources/views/components/inspirecms/{theme-name}/
├── layout.blade.php          # Main layout template
├── page.blade.php            # Standard page template
├── header.blade.php          # Header component
├── footer.blade.php          # Footer component
├── navigation.blade.php      # Navigation component
└── ... other components
```

## Default Themes

InspireCMS comes with a default theme called `manifest`. This theme includes:

- Responsive layout templates
- Basic page components
- Essential styling
- Common site elements (header, footer, navigation)

## Viewing and Changing Themes

### Viewing Available Themes

To see all available themes:

1. Navigate to **Settings → Templates** in the admin panel
2. The "Themes" section shows all installed themes
3. The current active theme is highlighted

### Changing the Active Theme

To switch themes:

1. Go to **Settings → Templates**
2. Find the theme you want to activate
3. Click "Change theme" next to that theme
4. Confirm the change

The theme change takes effect immediately on your site.

## Creating a New Theme

### Using the Admin Interface

1. Go to **Settings → Templates**
2. Click "Create theme"
3. Enter a name for your new theme
4. Choose whether to base it on an existing theme
5. Click "Create theme"

The new theme will be created in `resources/views/components/inspirecms/{your-theme-name}/`.

### Manual Creation

1. Create the theme directory structure:

```bash
mkdir -p resources/views/components/inspirecms/your-theme-name
```

2. Create the essential template files:

```bash
touch resources/views/components/inspirecms/your-theme-name/layout.blade.php
touch resources/views/components/inspirecms/your-theme-name/page.blade.php
```

3. Implement the basic templates:

```php
<!-- resources/views/components/inspirecms/your-theme-name/layout.blade.php -->
<!DOCTYPE html>
<html lang="{{ $locale ?? app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    @if (isset($seo) && $seo instanceof \Illuminate\Contracts\Support\Htmlable)
        {{ $seo }}
    @endif
    <!-- Your CSS and other head elements -->
</head>
<body>
    {{ $slot }}
</body>
</html>
```

```php
<!-- resources/views/components/inspirecms/your-theme-name/page.blade.php -->
@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();

    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
@endphp
<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale">
    <header>
        <!-- Header content -->
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer>
        <!-- Footer content -->
    </footer>
</x-dynamic-component>
```

## Cloning a Theme

To create a new theme based on an existing one:

1. Go to **Settings → Templates**
2. Find the theme you want to clone
3. Click "Clone theme"
4. Enter a name for the new theme
5. Click "Clone"

This copies all templates and components from the source theme to your new theme.

## Theme Components

Themes use Blade components to create reusable UI elements.

### Creating Theme Components

Create a new component in your theme:

```php
<!-- resources/views/components/inspirecms/your-theme/hero.blade.php -->
<section class="hero">
    <div class="hero-content">
        <h1>{{ $title ?? 'Welcome' }}</h1>
        @if(isset($subtitle))
            <p>{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
```

### Using Theme Components

Using the helper:

```php
@php
    $heroComponent = inspirecms_templates()->getComponentWithTheme('hero');
@endphp
<x-dynamic-component :component="$heroComponent" :title="$pageTitle">
    <p>Custom hero content here</p>
</x-dynamic-component>
```

## Theme Configuration

### Default Theme Setting

Set the default theme in your configuration:

```php
// config/inspirecms.php
'template' => [
    'default_theme' => 'your-theme',
    'component_prefix' => 'inspirecms',
    'exported_template_dir' => resource_path('views/inspirecms/templates'),
],
```

### Theme-specific Configuration

Create theme-specific configuration:

```php
// config/themes/your-theme.php
return [
    'assets' => [
        'css' => [
            '/themes/your-theme/css/main.css',
            '/themes/your-theme/css/custom.css',
        ],
        'js' => [
            '/themes/your-theme/js/main.js',
        ],
    ],
    'colors' => [
        'primary' => '#3490dc',
        'secondary' => '#ffed4a',
        'accent' => '#f66d9b',
    ],
    'fonts' => [
        'heading' => 'Montserrat, sans-serif',
        'body' => 'Open Sans, sans-serif',
    ],
];
```

Access theme configuration in templates:

```php
<div style="color: {{ config('themes.your-theme.colors.primary') }}">
    Themed content
</div>
```

## Theme Assets

### Asset Structure

Organize theme assets in the public directory:

```
public/themes/your-theme/
├── css/
│   ├── main.css
│   └── custom.css
├── js/
│   └── main.js
├── images/
│   └── logo.svg
└── fonts/
    ├── font-regular.woff2
    └── font-bold.woff2
```

### Including Assets

Include your assets in the theme's layout:

```php
<!-- resources/views/components/inspirecms/your-theme/layout.blade.php -->
<!DOCTYPE html>
<html lang="{{ $locale ?? app()->getLocale() }}">
<head>
    <!-- ... other head elements -->
    @foreach(config('themes.your-theme.assets.css', []) as $css)
        <link rel="stylesheet" href="{{ $css }}">
    @endforeach
    
    @stack('styles')
</head>
<body>
    {{ $slot }}
    
    @foreach(config('themes.your-theme.assets.js', []) as $js)
        <script src="{{ $js }}"></script>
    @endforeach
    
    @stack('scripts')
</body>
</html>
```

### Asset Versioning

For production environments, add versioning to prevent caching issues:

```php
<link rel="stylesheet" href="{{ asset('themes/your-theme/css/main.css') }}?v={{ config('themes.your-theme.version', '1.0.0') }}">
```

## Theme Templates

Templates define how content is displayed using your theme components.

### Default Templates

Each theme should provide at least these basic templates:

1. **Layout**: The base HTML structure
2. **Page**: Standard page template
3. **Simple Page**: Minimal page template for basic content

### Custom Template Types

Create specialized templates for different content types:

```php
<!-- resources/views/components/inspirecms/your-theme/blog-post.blade.php -->
@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();
    
    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
    $headerComponent = inspirecms_templates()->getComponentWithTheme('header');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
    $sidebarComponent = inspirecms_templates()->getComponentWithTheme('sidebar');
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale">
    <x-dynamic-component :component="$headerComponent" :locale="$locale" />
    
    <main class="blog-post-container">
        <article class="blog-post">
            <header>
                <h1>@property('blog', 'title')</h1>
                <p class="meta">
                    <time>@property('blog', 'date')</time>
                    <span class="author">@property('blog', 'author')</span>
                </p>
            </header>
            
            <div class="blog-content">
                @property('blog', 'content')
            </div>
            
            <footer>
                @propertyArray('blog', 'tags')
                <div class="tags">
                    @foreach($blog_tags ?? [] as $tag)
                        <span class="tag">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
            </footer>

        </article>
        
        <aside>
            <x-dynamic-component :component="$sidebarComponent" :content="$content" />
        </aside>
    </main>
    
    <x-dynamic-component :component="$footerComponent" :locale="$locale" />
</x-dynamic-component>
```

## Theme Inheritance

Theme inheritance allows child themes to extend parent themes:

### Creating a Child Theme

1. Create a new theme directory
2. Implement only the components you want to override
3. Configure the parent-child relationship

```php
// config/themes/your-child-theme.php
return [
    'parent' => 'your-parent-theme',
    // other configuration...
];
```

Then access parent theme components when needed:

```php
@php
    $parentComponent = inspirecms_templates()->getComponentWithTheme('sidebar', 'your-parent-theme');
@endphp
<x-dynamic-component :component="$parentComponent" />
```

## Responsive Design

Ensure your theme works across different devices:

```php
<!-- resources/views/components/inspirecms/your-theme/layout.blade.php -->
<!DOCTYPE html>
<html lang="{{ $locale ?? app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ...other head elements -->
    
    <style>
        /* Basic responsive styles */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 10px;
            }
        }
    </style>
</head>
<body>
    <!-- ...content -->
</body>
</html>
```

## Testing Themes

Verify your theme works properly:

1. Test with different content types
2. Test on various screen sizes and devices
3. Validate accessibility compliance
4. Check for browser compatibility

## Theme Development Best Practices

1. **Separation of Concerns**: Keep presentation logic out of templates
2. **Component Reusability**: Design reusable components
3. **CSS Organization**: Use a structured approach (e.g., BEM methodology)
4. **Accessibility**: Ensure themes meet WCAG guidelines
5. **Performance**: Minimize CSS/JS and optimize assets
6. **Documentation**: Document theme components and configuration options
7. **Version Control**: Track theme changes in version control

## Publishing Themes

To share your theme with others:

1. Package your theme files
2. Include documentation for installation and configuration
3. List required assets and dependencies
4. Provide example templates and screenshots

## Theme Marketplace

If your InspireCMS version supports it, you can:

1. Publish themes to the theme marketplace
2. Install themes from the marketplace
3. Rate and review installed themes
4. Contribute to community theme development
        