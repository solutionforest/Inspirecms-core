# Blade Templates in InspireCMS

InspireCMS leverages Laravel's powerful Blade templating engine to create dynamic and reusable frontend templates. This guide covers how to use Blade effectively within the InspireCMS environment, including custom directives, components, and best practices.

## Introduction to Blade

[Blade](https://laravel.com/docs/11.x/blade) is Laravel's templating engine that combines the simplicity of plain PHP with powerful features like template inheritance, components, and directives. In InspireCMS, Blade is used to build flexible and maintainable templates for your website.

### Basic Blade Syntax

Blade templates use the `.blade.php` extension and include standard HTML with special Blade directives:

```php
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

InspireCMS extends Blade with custom directives for accessing content properties and common CMS functions.

### Property Directives

Access content field values with these directives:

#### @property

Basic property access:

```php
<h1>@property('hero', 'title')</h1>
<!-- Output: "Welcome to our website" -->

<!-- With custom variable name -->
@property('hero', 'subtitle', 'custom_subtitle')
<p>{{ $custom_subtitle }}</p>
```

#### @propertyArray

Access array properties:

```php
@propertyArray('gallery', 'images')
@foreach($gallery_images ?? [] as $image)
    <img src="{{ $image->getUrl() }}" alt="{{ $image->caption }}">
@endforeach
```

#### @propertyNotEmpty

Conditionally display content when a property has a value:

```php
@propertyNotEmpty('hero', 'button_text')
    <a href="@property('hero', 'button_link')" class="btn">
        {{ $hero_button_text }}
    </a>
@endif
```

### Language and Localization Directives

Display content in multiple languages:

```php
<h1>@trans('welcome.title')</h1>

<div class="language-switcher">
    @foreach(inspirecms()->getAllAvailableLanguages() as $locale => $langDto)
        <a href="{{ url($locale) }}" @if(app()->getLocale() == $locale)class="active"@endif>
            {{ $langDto->getLabel() }}
        </a>
    @endforeach
</div>
```

## Blade Components in InspireCMS

InspireCMS uses Blade components for reusable UI elements.

### Using CMS Components

The `x-cms-template` component is the foundation of InspireCMS templates:

```php
<x-cms-template :content="$content" type="page">
    <h1>@property('banner', 'heading')</h1>
    <div class="content">@property('content', 'body')</div>
</x-cms-template>
```

### Theme Components

Access theme-specific components:

```php
@php
$headerComponent = inspirecms_templates()->getComponentWithTheme('header');
@endphp

<x-dynamic-component :component="$headerComponent" :title="$pageTitle">
    <!-- Custom content for the header component -->
</x-dynamic-component>
```

Create custom theme components:

```php
<!-- resources/views/components/inspirecms/my-theme/alert.blade.php -->
<div class="alert alert-{{ $type ?? 'info' }}">
    @if(isset($title))
        <h4>{{ $title }}</h4>
    @endif
    
    <div class="alert-content">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="alert-footer">
            {{ $footer }}
        </div>
    @endif
</div>
```

Use your component:

```php
<x-dynamic-component :component="inspirecms_templates()->getComponentWithTheme('alert')" type="warning" title="Important Notice">
    This is an important message for all users.
    
    <x-slot:footer>
        <button>Dismiss</button>
    </x-slot>
</x-dynamic-component>
```

## Blade Layouts and Template Inheritance

InspireCMS leverages Blade's template inheritance for creating consistent layouts.

### Basic Layout Structure

Create a master layout:

```php
<!-- resources/views/layouts/master.blade.php -->
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name') }}</title>
    
    <!-- Meta tags -->
    @yield('meta')
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="@yield('body-class')">
    <header>
        @include('partials.header')
    </header>
    
    <main>
        @yield('content')
    </main>
    
    <footer>
        @include('partials.footer')
    </footer>
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
```

Extend the layout in templates:

```php
<!-- resources/views/templates/blog-post.blade.php -->
@extends('layouts.master')

@section('title', $content->getTitle())

@section('meta')
    {!! $content->getSeo()?->getHtml() !!}
@endsection

@section('body-class', 'blog-post-page')

@section('content')
    <article class="blog-post">
        <header>
            <h1>@property('blog', 'title')</h1>
            <p class="meta">
                <time>@property('blog', 'date')</time>
                <span class="author">@property('blog', 'author')</span>
            </p>
        </header>
        
        <div class="content">
            @property('blog', 'content')
        </div>
        
        @propertyNotEmpty('blog', 'tags')
            <div class="tags">
                @foreach($blog_tags as $tag)
                    <span class="tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </article>
@endsection
```

## Advanced Blade Techniques

### Template Partials

Break your templates into manageable pieces:

```php
<!-- resources/views/partials/hero.blade.php -->
<section class="hero" style="background-image: url('{{ $background ?? '' }}')">
    <div class="hero-content">
        <h1>{{ $title }}</h1>
        @if(isset($subtitle))
            <p>{{ $subtitle }}</p>
        @endif
        
        @if(isset($cta_url) && isset($cta_text))
            <a href="{{ $cta_url }}" class="btn btn-primary">{{ $cta_text }}</a>
        @endif
    </div>
</section>
```

Include the partial:

```php
@include('partials.hero', [
    'title' => $hero_title,
    'subtitle' => $hero_subtitle ?? null,
    'background' => $hero_image?->getUrl(),
    'cta_url' => $hero_button_link ?? '#',
    'cta_text' => $hero_button_text ?? 'Learn More'
])
```

### Template Slots

Use slots for flexible component content:

```php
<!-- resources/views/components/card.blade.php -->
<div class="card {{ $class ?? '' }}">
    @if(isset($header))
        <div class="card-header">
            {{ $header }}
        </div>
    @endif
    
    <div class="card-body">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
```

Use the component with slots:

```php
<x-card class="featured-card">
    <x-slot:header>
        <h3>Featured Article</h3>
    </x-slot>
    
    <p>This is the main content of the card.</p>
    
    <x-slot:footer>
        <a href="#">Read more</a>
    </x-slot>
</x-card>
```

### Conditional Rendering

Render content conditionally:

```php
@if($content->hasProperty('sidebar', 'show') && $content->getPropertyValue('sidebar', 'show'))
    <aside class="sidebar">
        @property('sidebar', 'content')
    </aside>
@endif
```

Use the `@unless` directive for negative conditions:

```php
@unless($hideFooter)
    <footer>
        <!-- Footer content -->
    </footer>
@endunless
```

### Loops and Iteration

Work with collections and arrays:

```php
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

Use the `@foreach` directive with loop variable:

```php
@foreach($gallery_images as $image)
    <div class="gallery-item {{ $loop->first ? 'first' : '' }} {{ $loop->last ? 'last' : '' }}">
        <img src="{{ $image->getUrl() }}" alt="{{ $image->caption }}">
        <span class="number">{{ $loop->iteration }}/{{ $loop->count }}</span>
    </div>
@endforeach
```

## Custom Blade Directives

Create custom Blade directives for specialized template functions.

### Registering Custom Directives

Register in a service provider:

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
        
        // Complex directive with parameters
        Blade::directive('contentLink', function ($expression) {
            $params = explode(',', $expression);
            $id = trim($params[0]);
            $locale = isset($params[1]) ? trim($params[1]) : 'null';

            return "<?php echo inspirecms_content()->findByIds(ids: $id, limit: 1)?->getUrl($locale); ?>";
        });
    }
}
```

Use custom directives:

```php
<p>Posted on @formatDate($content->published_at)</p>

<a href="@contentLink('550e8400-e29b-41d4-a716-446655440000', 'en')">
    <!-- Link content -->
</a>
```

## Caching Templates

Improve performance with template caching:

### View Caching

Cache compiled views in production:

```bash
php artisan view:cache
```

Clear the cache during development:

```bash
php artisan view:clear
```

### Fragment Caching

Cache specific sections of your templates:

```php
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

## Common Blade Patterns in InspireCMS

### SEO Meta Tags

Handle dynamic SEO meta tags:

```php
<head>
    <!-- Basic meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @if($content && $content->getSeo())
        <!-- Structured SEO data from content -->
        {!! $content->getSeo()?->getHtml() !!}
    @else
        <!-- Fallback meta tags -->
        <title>{{ $title ?? config('app.name') }}</title>
        <meta name="description" content="{{ $description ?? config('app.description') }}">
    @endif
    
    <!-- Open Graph tags -->
    <meta property="og:title" content="{{ $content->getTitle() ?? config('app.name') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @propertyNotEmpty('social', 'og_image')
        <meta property="og:image" content="{{ $social_og_image->getUrl() }}">
    @endif
</head>
```

### Navigation Menus

Create flexible navigation menus:

```php
@php
    $navigation = inspirecms()->getNavigation('main', app()->getLocale());
    $currentUrl = url()->current();
@endphp

<nav class="main-navigation">
    <ul>
        @foreach($navigation as $item)
            @php
                $isActive = $currentUrl == $item->getUrl() || 
                            (request()->segment(1) == $item->getSlug() && $item->getSlug() !== '');
                $hasChildren = $item->hasChildren();
            @endphp
            
            <li class="{{ $isActive ? 'active' : '' }} {{ $hasChildren ? 'has-children' : '' }}">
                <a href="{{ $item->getUrl() }}" {{ $item->getTarget() == '_blank' ? 'target="_blank"' : '' }}>
                    {{ $item->getTitle() }}
                </a>
                
                @if($hasChildren)
                    <ul class="sub-menu">
                        @foreach($item->children as $child)
                            @php
                                $isChildActive = $currentUrl == $child->getUrl();
                            @endphp
                            
                            <li class="{{ $isChildActive ? 'active' : '' }}">
                                <a href="{{ $child->getUrl() }}" {{ $child->getTarget() == '_blank' ? 'target="_blank"' : '' }}>
                                    {{ $child->getTitle() }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
```

### Breadcrumbs

Implement breadcrumb navigation:

```php
@php
    $breadcrumbs = $content->getAncestors()->toArray();
    $breadcrumbs[] = $content;
@endphp

<nav aria-label="Breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}">Home</a>
        </li>
        
        @foreach($breadcrumbs as $index => $crumb)
            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                @if($loop->last)
                    {{ $crumb->getTitle() }}
                @else
                    <a href="{{ $crumb->getUrl() }}">{{ $crumb->getTitle() }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
```

## Best Practices

1. **Keep Templates Clean**: Avoid business logic in templates
2. **Use Components**: Break UI into reusable components
3. **Template Inheritance**: Build layouts with extends and sections
4. **Escape Output**: Use `{{ }}` for escaped output and `{!! !!}` only when HTML is needed
5. **Optimize Performance**: Use caching for complex templates
6. **Follow Naming Conventions**: Use consistent naming for partials and components
7. **Include Comments**: Document complex template sections
8. **Mobile-First**: Design templates with responsive design in mind
9. **Test Templates**: Verify templates render correctly in different scenarios
10. **Follow Accessibility**: Ensure templates produce accessible HTML

## Troubleshooting Common Issues

### Property Not Found

If your template shows an error about a property not being found:

```
Check if the field group exists in the content type
Check if the field slug matches exactly (case-sensitive)
Ensure the content has data for that property
```

Solution:

```php
@if($content->hasPropertyGroup('hero') && $content->hasProperty('hero', 'title'))
    <h1>@property('hero', 'title')</h1>
@else
    <h1>Default Title</h1>
@endif
```

### Template Performance Issues

If templates render slowly:

```
Use fragment caching for expensive sections
Optimize database queries by eager loading relationships
Avoid complex logic in templates
Use pagination for large collections
```

Example optimization:

```php
@php
// Cache expensive partial for 30 minutes
$sidebarCache = Cache::remember('sidebar_'.$content->id.'_'.app()->getLocale(), 30*60, function() use ($content) {
    // Complex sidebar generation logic
    return view('partials.sidebar', ['content' => $content])->render();
});
@endphp

{!! $sidebarCache !!}
```

### Multilingual Content Rendering

For issues with multilingual content:

```
Ensure the content has translations for the current locale
Provide fallbacks for missing translations
Check if the property is marked as translatable
```

Solution:

```php
@php
$locale = app()->getLocale();
$fallbackLocale = config('app.fallback_locale');

// Get title in current language or fallback to default language
$title = $content->getPropertyValue('hero', 'title', $locale, $fallbackLocale) 
    ?? 'Default Title';
@endphp

<h1>{{ $title }}</h1>
```

## Complex Data Rendering Examples

### Rendering Structured Content

For complex content structures:

```php
@propertyArray('page', 'sections')
@foreach($page_sections as $section)
    <section class="content-section {{ $section['type'] ?? 'default' }}-section">
        <div class="container">
            @if(!empty($section['heading']))
                <h2>{{ $section['heading'] }}</h2>
            @endif
            
            @if(!empty($section['content']))
                <div class="section-content">
                    {!! $section['content'] !!}
                </div>
            @endif
            
            @if(!empty($section['media']))
                <div class="section-media">
                    @foreach($section['media'] as $mediaId)
                        @php $media = inspirecms_asset()->findByKey($mediaId); @endphp
                        @if($media)
                            <figure>
                                <img src="{{ $media->getUrl() }}" alt="{{ $media->alt_text }}">
                                @if($media->caption)
                                    <figcaption>{{ $media->caption }}</figcaption>
                                @endif
                            </figure>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endforeach
```

### Dynamic Form Rendering

Create forms based on template data:

```php
<form action="{{ route('contact.submit') }}" method="POST" class="dynamic-form">
    @csrf
    
    @propertyArray('form', 'fields')
    @foreach($form_fields as $field)
        <div class="form-group {{ $field['width'] ?? 'full' }}-width">
            <label for="field_{{ $loop->index }}">
                {{ $field['label'] }}
                @if(!empty($field['required']))
                    <span class="required">*</span>
                @endif
            </label>
            
            @switch($field['type'] ?? 'text')
                @case('textarea')
                    <textarea 
                        name="{{ $field['name'] }}" 
                        id="field_{{ $loop->index }}"
                        class="form-control"
                        rows="{{ $field['rows'] ?? 4 }}"
                        {{ !empty($field['required']) ? 'required' : '' }}
                    >{{ old($field['name']) }}</textarea>
                    @break
                
                @case('select')
                    <select 
                        name="{{ $field['name'] }}" 
                        id="field_{{ $loop->index }}"
                        class="form-control"
                        {{ !empty($field['required']) ? 'required' : '' }}
                    >
                        <option value="">Select an option</option>
                        @foreach($field['options'] ?? [] as $option)
                            <option value="{{ $option['value'] }}" {{ old($field['name']) == $option['value'] ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @break
                    
                @default
                    <input 
                        type="{{ $field['type'] ?? 'text' }}"
                        name="{{ $field['name'] }}"
                        id="field_{{ $loop->index }}"
                        class="form-control"
                        value="{{ old($field['name']) }}"
                        {{ !empty($field['required']) ? 'required' : '' }}
                        {{ !empty($field['placeholder']) ? 'placeholder='.$field['placeholder'] : '' }}
                    >
            @endswitch
            
            @error($field['name'])
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
    @endforeach
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            @propertyNotEmpty('form', 'submit_text')
                {{ $form_submit_text }}
            @else
                Submit
            @endif
        </button>
    </div>
</form>
```

## Custom Helpers for Templates

Create blade-specific helper functions:

```php
// app/helpers.php
if (!function_exists('format_content_date')) {
    function format_content_date($date, $format = 'F j, Y')
    {
        return $date ? date($format, strtotime($date)) : '';
    }
}

if (!function_exists('get_reading_time')) {
    function get_reading_time($content, $wordsPerMinute = 200)
    {
        $wordCount = str_word_count(strip_tags($content));
        $minutes = ceil($wordCount / $wordsPerMinute);
        return $minutes < 1 ? 1 : $minutes;
    }
}
```

Use helpers in templates:

```php
<p class="post-meta">
    Published on 
    <time datetime="{{ $content->published_at?->format('Y-m-d') }}">
        {{ format_content_date($content->published_at) }}
    </time>
    &bull; {{ get_reading_time($content->getPropertyValue('blog', 'body')) }} min read
</p>
```

## Integrating JavaScript with Blade

Pass data from PHP to JavaScript safely:

```php
<script>
    // Encode data safely for JavaScript
    window.pageConfig = @json([
        'contentId' => $content->id,
        'locale' => app()->getLocale(),
        'csrfToken' => csrf_token(),
        'apiBaseUrl' => url('/api'),
        'settings' => [
            'commentsEnabled' => $content->getPropertyValue('settings', 'enable_comments', false),
            'shareEnabled' => $content->getPropertyValue('settings', 'enable_social_sharing', true)
        ]
    ]);
</script>
```

## Working with Assets in Templates

Handle assets effectively:

```php
<!-- Define assets based on environment -->
@php
    $assetVersion = config('app.env') === 'production' ? config('inspirecms.version') : time();
    
    // Get theme-specific assets
    $themeConfig = config('themes.' . inspirecms_templates()->getCurrentTheme());
    $themeAssets = $themeConfig['assets'] ?? [];
@endphp

<!-- CSS -->
@foreach($themeAssets['css'] ?? [] as $css)
    <link rel="stylesheet" href="{{ asset($css) }}?v={{ $assetVersion }}">
@endforeach

<!-- Deferred JS loading -->
@foreach($themeAssets['js'] ?? [] as $js)
    <script src="{{ asset($js) }}?v={{ $assetVersion }}" defer></script>
@endforeach
```

With these techniques and examples, you'll be able to create sophisticated, maintainable, and efficient templates for your InspireCMS website.