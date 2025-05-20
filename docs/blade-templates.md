---
title: Blade Templates
slug: blade-templates
path: docs/v1/blade-templates
uri: /docs/1.x/blade-templates
---
# Blade Templates

InspireCMS leverages Laravel's powerful Blade templating engine to create dynamic templates. This guide covers core Blade functionality specific to InspireCMS.

---

## Introduction to Blade

[Blade](https://laravel.com/docs/11.x/blade) is Laravel's templating engine that combines plain PHP with powerful features like template inheritance, components, and directives.

### Basic Blade Syntax

```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $pageTitle }}</title>
</head>
<body>
    <h1>{{ $heading }}</h1>

    @if($showContent)
        <div class="content">
            {!! $content !!}
        </div>
    @endif

    <footer>
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </footer>
</body>
</html>
```

## InspireCMS-Specific Blade Directives

InspireCMS extends Blade with custom directives for accessing content properties and CMS functions.

### Property Directives

> [!note]
> For comprehensive coverage of property directives, see the [Content](./fe-content){.doc-link} documentation.

```php
<!-- Basic property access -->
<h1>@property('hero', 'title')</h1>

<!-- Conditional display -->
@propertyNotEmpty('hero', 'button_text')
    <a href="@property('hero', 'button_link')" class="btn">
        {{ $hero_button_text }}
    </a>
@endif
```

### Language and Localization Directives

```blade
<h1>@trans('welcome.title')</h1>

<div class="language-switcher">
    @foreach(inspirecms()->getAllAvailableLanguages() as $locale => $langDto)
        <a href="{{ url($locale) }}" @if(app()->getLocale() == $locale)class="active"@endif>
            {{ $langDto->getLabel() }}
        </a>
    @endforeach
</div>
```

> [!note]
> For layouts and template inheritance, see the [Layouts](./layouts){.doc-link} documentation.

> [!note]
> For components and reusable UI elements, see the [Components](./components){.doc-link} documentation.

> [!note]
> For template implementation examples, see the [Templates](./templates){.doc-link} documentation.

---

## Advanced Blade Techniques

### Template Partials

```blade {title="resources/views/partials/hero.blade.php"}
<section class="hero" style="background-image: url('{{ $background ?? '' }}')">
    <div class="hero-content">
        <h1>{{ $title }}</h1>
        @if(isset($subtitle))
            <p>{{ $subtitle }}</p>
        @endif
    </div>
</section>
```

```blade
@include('partials.hero', [
    'title' => $hero_title,
    'subtitle' => $hero_subtitle ?? null,
    'background' => $hero_image?->getUrl()
])
```

### Conditional Rendering

```blade
@if($content->hasProperty('sidebar', 'show') && $content->getPropertyValue('sidebar', 'show'))
    <aside class="sidebar">
        @property('sidebar', 'content')
    </aside>
@endif

@unless($hideFooter)
    <footer>
        <!-- Footer content -->
    </footer>
@endunless
```

### Loops and Iteration

```blade
@propertyArray('blog', 'related_posts')
@forelse($blog_related_posts ?? [] as $post)
    <div class="related-post">
        <h3><a href="{{ $post->getUrl() }}">{{ $post->getTitle() }}</a></h3>
        <p>{{ Str::limit($post->getPropertyValue('blog', 'excerpt'), 100) }}</p>
    </div>
@empty
    <p>No related posts found.</p>
@endforelse
```

---

## Custom Blade Directives

Create custom Blade directives for specialized template functions:

```php
namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Simple directive
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo date('F j, Y', strtotime($expression)); ?>";
        });

        // Complex directive
        Blade::directive('contentLink', function ($expression) {
            $params = explode(',', $expression);
            $id = trim($params[0]);
            $locale = isset($params[1]) ? trim($params[1]) : 'null';

            return "<?php echo inspirecms_content()->findByIds(ids: $id, limit: 1)?->getUrl($locale); ?>";
        });
    }
}
```

---

## Caching Templates

### View Caching

```bash
# Cache compiled views in production
php artisan view:cache

# Clear the cache during development
php artisan view:clear
```

### Fragment Caching

```blade
@php
$cacheKey = "content_preview_{$content->id}_" . app()->getLocale();
$cacheTtl = 60 * 24; // 24 hours in minutes
@endphp

@cache($cacheKey, $cacheTtl)
    <div class="content-preview">
        <h2>{{ $content->getTitle() }}</h2>
        <div class="excerpt">
            {!! Str::limit(strip_tags($content->getPropertyValue('content', 'body')), 200) !!}
        </div>
    </div>
@endcache
```

---

## Common Patterns

### SEO Meta Tags

```php
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @if($content && $content->getSeo())
        {!! $content->getSeo()?->getHtml() !!}
    @else
        <title>{{ $title ?? config('app.name') }}</title>
        <meta name="description" content="{{ $description ?? config('app.description') }}">
    @endif
</head>
```

### Integrating JavaScript with Blade

```html
<script>
    // Encode data safely for JavaScript
    window.pageConfig = @json([
        'contentId' => $content->id,
        'locale' => app()->getLocale(),
        'csrfToken' => csrf_token(),
        'apiBaseUrl' => url('/api')
    ]);
</script>
```

---

## Best Practices

1. **Keep Templates Clean**: Avoid business logic in templates
2. **Use Components**: Break UI into reusable components
3. **Escape Output**: Use `{{ }}` for escaped output and `{!! !!}` only when HTML is needed
4. **Optimize Performance**: Use caching for complex templates
5. **Follow Naming Conventions**: Use consistent naming for partials and components

For more detailed implementation examples and troubleshooting tips, see the [Templates](./templates){.doc-link} documentation.
