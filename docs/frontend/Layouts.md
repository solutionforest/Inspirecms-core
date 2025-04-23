# Layouts

Learn how to create and use layouts in InspireCMS to build consistent page structures.

## Layout Overview

Layouts in InspireCMS define the overall structure of your pages, allowing you to maintain a consistent look and feel across your site. InspireCMS supports two approaches to layouts:

1. **Component-Based Layouts**: Using Blade components
2. **Template Inheritance**: Using Blade's `@extends` directive

## Component-Based Layouts

Component-based layouts use Blade components to build the page structure.

### Creating a Base Layout Component

Create a base layout component for your theme:

```php
<!-- resources/views/components/inspirecms/your-theme/layout.blade.php -->
@props([
    'title' => config('app.name'),
    'description' => null,
    'seo' => null,
    'locale' => app()->getLocale(),
])

<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO tags -->
    @if($seo)
        {!! $seo !!}
    @else
        <title>{{ $title }}</title>
        @if($description)
            <meta name="description" content="{{ $description }}">
        @endif
    @endif
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    
    <!-- CSS -->
    @php
        $themeConfig = config('themes.' . inspirecms_templates()->getCurrentTheme());
        $themeAssets = $themeConfig['assets'] ?? [];
    @endphp
    
    @foreach($themeAssets['css'] ?? [] as $css)
        <link rel="stylesheet" href="{{ asset($css) }}?v={{ config('inspirecms.version', time()) }}">
    @endforeach
    
    <!-- Custom styles -->
    @stack('styles')
</head>
<body class="{{ $attributes->get('bodyClass', '') }}">
    <!-- Content -->
    {{ $slot }}
    
    <!-- JavaScript -->
    @foreach($themeAssets['js'] ?? [] as $js)
        <script src="{{ asset($js) }}?v={{ config('inspirecms.version', time()) }}" defer></script>
    @endforeach
    
    <!-- Custom scripts -->
    @stack('scripts')
</body>
</html>
```

### Header and Footer Components

Create header and footer components to use with your layout:

```php
<!-- resources/views/components/inspirecms/your-theme/header.blade.php -->
@props(['locale' => app()->getLocale()])

<header class="site-header">
    <div class="container">
        <div class="header-content">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
            </a>
            
            <nav class="main-navigation">
                <ul>
                    @foreach(inspirecms()->getNavigation('main', $locale) as $item)
                        <li class="{{ url()->current() == $item->getUrl() ? 'active' : '' }}">
                            <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
                            
                            @if($item->hasChildren())
                                <ul class="sub-menu">
                                    @foreach($item->children as $child)
                                        <li class="{{ url()->current() == $child->getUrl() ? 'active' : '' }}">
                                            <a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
            
            <button class="menu-toggle" aria-label="Toggle Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>
```

```php
<!-- resources/views/components/inspirecms/your-theme/footer.blade.php -->
@props(['locale' => app()->getLocale()])

<footer class="site-footer">
    <div class="container">
        <div class="footer-widgets">
            @foreach(inspirecms()->getNavigation('footer', $locale) as $item)
                <div class="footer-widget">
                    <h4 class="widget-title">{{ $item->getTitle() }}</h4>
                    
                    @if($item->hasChildren())
                        <ul class="widget-menu">
                            @foreach($item->children as $child)
                                <li>
                                    <a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
        
        <div class="footer-bottom">
            <p class="copyright">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
            
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="icon icon-facebook"></i></a>
                <a href="#" aria-label="Twitter"><i class="icon icon-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="icon icon-instagram"></i></a>
            </div>
        </div>
    </div>
</footer>
```

### Content Layout Component

Create a content layout that uses the base layout:

```php
<!-- resources/views/components/inspirecms/your-theme/page.blade.php -->
@props(['content', 'locale' => null])

@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();
    
    $headerComponent = inspirecms_templates()->getComponentWithTheme('header');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale" bodyClass="page-template">
    <x-dynamic-component :component="$headerComponent" :locale="$locale" />
    
    <main class="site-main">
        <article class="page-content">
            {{ $slot }}
        </article>
    </main>
    
    <x-dynamic-component :component="$footerComponent" :locale="$locale" />
</x-dynamic-component>
```

### Using Component-Based Layouts

To use your layout in a template:

```php
<x-cms-template :content="$content" type="page">
    <div class="container">
        <header class="page-header">
            <h1>@property('hero', 'title')</h1>
            @propertyNotEmpty('hero', 'subtitle')
                <p class="subtitle">{{ $hero_subtitle }}</p>
            @endif
        </header>
        
        <div class="page-body">
            @property('content', 'body')
        </div>
    </div>
</x-cms-template>
```

## Template Inheritance

Template inheritance uses Blade's `@extends`, `@section`, and `@yield` directives to build layouts.

### Creating a Master Layout

Create a master layout file:

```php
<!-- resources/views/layouts/inspirecms/your-theme/master.blade.php -->
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta -->
    @yield('seo')
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    
    <!-- CSS -->
    @php
        $currentTheme = inspirecms_templates()->getCurrentTheme();
        $themeConfig = config('themes.' . $currentTheme);
        $themeAssets = $themeConfig['assets'] ?? [];
    @endphp
    
    @foreach($themeAssets['css'] ?? [] as $css)
        <link rel="stylesheet" href="{{ asset($css) }}?v={{ config('inspirecms.version', time()) }}">
    @endforeach
    
    @stack('styles')
</head>
<body class="@yield('body-class')">
    
    @yield('content')
    
    <!-- JavaScript -->
    @foreach($themeAssets['js'] ?? [] as $js)
        <script src="{{ asset($js) }}?v={{ config('inspirecms.version', time()) }}" defer></script>
    @endforeach
    
    @stack('scripts')
</body>
</html>
```

### Creating Partials

Create header and footer partials:

```php
<!-- resources/views/layouts/inspirecms/your-theme/partials/header.blade.php -->
<div class="container">
    <div class="header-content">
        <a href="{{ url('/') }}" class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
        </a>
        
        <nav class="main-navigation">
            <ul>
                @foreach(inspirecms()->getNavigation('main', app()->getLocale()) as $item)
                    <li class="{{ url()->current() == $item->getUrl() ? 'active' : '' }}">
                        <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
                        
                        @if($item->hasChildren())
                            <ul class="sub-menu">
                                @foreach($item->children as $child)
                                    <li class="{{ url()->current() == $child->getUrl() ? 'active' : '' }}">
                                        <a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
        
        <button class="menu-toggle" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</div>
```

```php
<!-- resources/views/layouts/inspirecms/your-theme/partials/footer.blade.php -->
<div class="container">
    <div class="footer-widgets">
        @foreach(inspirecms()->getNavigation('footer', app()->getLocale()) as $item)
            <div class="footer-widget">
                <h4 class="widget-title">{{ $item->getTitle() }}</h4>
                
                @if($item->hasChildren())
                    <ul class="widget-menu">
                        @foreach($item->children as $child)
                            <li>
                                <a href="{{ $child->getUrl() }}">{{ $child->getTitle() }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>
    
    <div class="footer-bottom">
        <p class="copyright">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
        
        <div class="social-links">
            <a href="#" aria-label="Facebook"><i class="icon icon-facebook"></i></a>
            <a href="#" aria-label="Twitter"><i class="icon icon-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="icon icon-instagram"></i></a>
        </div>
    </div>
</div>
```

### Creating Page Templates

Create a page template that extends the master layout:

```php
<!-- resources/views/templates/inspirecms/your-theme/page.blade.php -->
@php
    $currentTheme = inspirecms_templates()->getCurrentTheme();
@endphp
@extends('layouts.inspirecms.'. $currentTheme.'.master')

@section('content')

    <header class="site-header">
        @include('layouts.inspirecms.' . $currentTheme . '.partials.header')
    </header>

    <main class="site-main">
        @yield('page-content', 'No content found')
    </main>

    <footer class="site-footer">
        @include('layouts.inspirecms.' . $currentTheme . '.partials.footer')
    </footer>

@endsection
```

### Using Template Inheritance

To use your layout in a template:

```php
@extends('layouts.inspirecms.'. inspirecms_templates()->getCurrentTheme() .'.page')

@section('body-class', 'blog-post-page')

@section('page-content')
    <div class="container">
        <header class="page-header">
            <h1>@property('hero', 'title')</h1>
            @propertyNotEmpty('hero', 'subtitle')
                <p class="subtitle">{{ $hero_subtitle }}</p>
            @endif
        </header>
        
        <div class="page-body">
            @property('content', 'body')
        </div>
    </div>
@endsection
```

## Specialized Layouts

Create specialized layouts for different content types:

### Blog Layout

```php
<!-- resources/views/components/inspirecms/your-theme/blog-layout.blade.php -->
@props(['content', 'locale' => null])

@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();
    
    $headerComponent = inspirecms_templates()->getComponentWithTheme('header');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
    
    // Get sidebar content
    $recentPosts = inspirecms_content()
        ->getPaginatedByDocumentType(documentType: 'blog_post', perPage: 5, isPublished: true, sorting: ['__latest_version_publish_dt' => 'desc'])
        ->toDto()
        ->items();
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale" bodyClass="blog-template">
    <x-dynamic-component :component="$headerComponent" :locale="$locale" />
    
    <main class="site-main">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Main content area -->
                    <article class="blog-content">
                        {{ $slot }}
                    </article>
                </div>
                
                <div class="col-lg-4">
                    <!-- Sidebar -->
                    <aside class="blog-sidebar">
                        <div class="widget">
                            <h3 class="widget-title">Recent Posts</h3>
                            <ul class="recent-posts">
                                @foreach($recentPosts as $post)
                                    <li>
                                        <a href="{{ $post->getUrl() }}">{{ $post->getTitle() }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        
                        {{ $sidebar ?? '' }}
                    </aside>
                </div>
            </div>
        </div>
    </main>
    
    <x-dynamic-component :component="$footerComponent" :locale="$locale" />
</x-dynamic-component>
```

### Using the Blog Layout

```php
<x-cms-template :content="$content" type="blog">
    <header class="blog-header">
        <h1>@property('blog', 'title')</h1>
        
        <div class="blog-meta">
            <span class="date">
                <time datetime="{{ $content->published_at?->format('Y-m-d') }}">
                    {{ $content->published_at?->format('F j, Y') }}
                </time>
            </span>
            
            @propertyNotEmpty('blog', 'author')
                <span class="author">by {{ $blog_author }}</span>
            @endif
        </div>
    </header>
    
    @propertyNotEmpty('blog', 'featured_image')
        <figure class="featured-image">
            <img src="{{ $blog_featured_image->getUrl() }}" alt="{{ $blog_featured_image->caption ?? '' }}">
        </figure>
    @endif
    
    <div class="blog-body">
        @property('blog', 'content')
    </div>
    
    <x-slot:sidebar>
        @propertyArray('blog', 'categories')
            <div class="widget">
                <h3 class="widget-title">Categories</h3>
                <ul class="categories">
                    @foreach($blog_categories as $category)
                        <li>{{ $category }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @propertyArray('blog', 'tags')
            <div class="widget">
                <h3 class="widget-title">Tags</h3>
                <div class="tags">
                    @foreach($blog_tags as $tag)
                        <span class="tag">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </x-slot>
</x-cms-template>
```

## Layout Options

Create layouts with different options:

### Full-Width Layout

```php
<!-- resources/views/components/inspirecms/your-theme/full-width.blade.php -->
@props(['content', 'locale' => null])

@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();
    
    $headerComponent = inspirecms_templates()->getComponentWithTheme('header');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale" bodyClass="full-width-template">
    <x-dynamic-component :component="$headerComponent" :locale="$locale" />
    
    <main class="site-main full-width">
        {{ $slot }}
    </main>
    
    <x-dynamic-component :component="$footerComponent" :locale="$locale" />
</x-dynamic-component>
```

### Landing Page Layout

```php
<!-- resources/views/components/inspirecms/your-theme/landing-page.blade.php -->
@props(['content', 'locale' => null])

@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();
    
    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale" bodyClass="landing-page-template">
    {{ $slot }}
    
    <footer class="landing-footer">
        <div class="container">
            <p class="copyright">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </footer>
</x-dynamic-component>
```

## Handling Multi-Language Layouts

Adapt layouts for multiple languages:

```php
<!-- resources/views/components/inspirecms/your-theme/language-switcher.blade.php -->
<div class="language-switcher">
    @foreach(inspirecms()->getAllAvailableLanguages() as $localeCode => $langDto)
        <a 
            href="{{ url($localeCode . request()->decodedPath()) }}"
            class="{{ app()->getLocale() == $localeCode ? 'active' : '' }}"
        >
            {{ $langDto->getLabel() }}
        </a>
    @endforeach
</div>
```

Add the language switcher to your header component:

```php
<!-- resources/views/components/inspirecms/your-theme/language-switcher.blade.php -->
@php
    $langs = inspirecms()->getAllAvailableLanguages();
    $localeCodes = collect($langs)->keys()->all();
@endphp
<div class="language-switcher">
    @foreach($langs as $localeCode => $langDto)
        @php
            $path = preg_replace(array_map(fn($code) => '/^' . preg_quote($code, '/') . '\//', $localeCodes) , '', request()->decodedPath());
        @endphp
        <a 
            href="{{ url("{$localeCode}/{$path}") }}"
            class="{{ app()->getLocale() == $localeCode ? 'active' : '' }}"
        >
            {{ $langDto->getLabel() }}
        </a>
    @endforeach
</div>
```

Add the language switcher to your header component:

```php
<!-- resources/views/components/inspirecms/your-theme/header.blade.php -->
<header class="site-header">
    <div class="container">
        <div class="header-content">
            <!-- ... other header content ... -->
            
            <x-dynamic-component :component="inspirecms_templates()->getComponentWithTheme('language-switcher')" />
        </div>
    </div>
</header>
```

### RTL Support

Add support for right-to-left languages:

```php
<!-- resources/views/components/inspirecms/your-theme/layout.blade.php -->
@props([
    'title' => config('app.name'),
    'description' => null,
    'seo' => null,
    'locale' => app()->getLocale(),
])

@php
    $direction = in_array($locale, ['ar', 'he', 'fa', 'ur']) ? 'rtl' : 'ltr';
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <!-- ... head content ... -->
    
    <!-- RTL-specific styles -->
    @if($direction === 'rtl')
        <link rel="stylesheet" href="{{ asset('css/rtl.css') }}?v={{ config('inspirecms.version', time()) }}">
    @endif
</head>
<body class="{{ $attributes->get('bodyClass', '') }} {{ $direction }}">
    <!-- ... body content ... -->
</body>
</html>
```

## Layout Composition

Build composite layouts using slot techniques:

### Sidebar Layout Component

```php
<!-- resources/views/components/inspirecms/your-theme/sidebar-layout.blade.php -->
@props([
    'content', 
    'locale' => null,
    'sidebarPosition' => 'right'
])

@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();
    
    $headerComponent = inspirecms_templates()->getComponentWithTheme('header');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale" bodyClass="sidebar-template sidebar-{{ $sidebarPosition }}">
    <x-dynamic-component :component="$headerComponent" :locale="$locale" />
    
    <main class="site-main">
        <div class="container">
            <div class="content-with-sidebar">
                @if($sidebarPosition === 'left')
                    <aside class="sidebar">
                        {{ $sidebar ?? '' }}
                    </aside>
                @endif
                
                <div class="main-content">
                    {{ $slot }}
                </div>
                
                @if($sidebarPosition === 'right')
                    <aside class="sidebar">
                        {{ $sidebar ?? '' }}
                    </aside>
                @endif
            </div>
        </div>
    </main>
    
    <x-dynamic-component :component="$footerComponent" :locale="$locale" />
</x-dynamic-component>
```

### Using Sidebar Layout

```php
<x-cms-template :content="$content" type="sidebar-page">
    <div class="page-content">
        <h1>@property('page', 'title')</h1>
        @property('page', 'body')
    </div>
    
    <x-slot:sidebar>
        <div class="widget">
            <h3 class="widget-title">Quick Links</h3>
            <ul>
                <li><a href="#">Link One</a></li>
                <li><a href="#">Link Two</a></li>
                <li><a href="#">Link Three</a></li>
            </ul>
        </div>
        
        @propertyNotEmpty('sidebar', 'content')
            <div class="widget">
                {{ $sidebar_content }}
            </div>
        @endif
    </x-slot>
</x-cms-template>
```

## Dynamic Layouts

Create layouts that adapt based on content properties:

```php
<!-- resources/views/components/inspirecms/your-theme/dynamic-page.blade.php -->
@props(['content', 'locale' => null])

@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();
    
    // Determine layout based on content properties
    $layoutStyle = $content->getPropertyValue('layout', 'style', 'standard');
    $hasSidebar = $content->getPropertyValue('layout', 'show_sidebar', false);
    $sidebarPosition = $content->getPropertyValue('layout', 'sidebar_position', 'right');
    
    // Choose appropriate layout components
    $headerComponent = inspirecms_templates()->getComponentWithTheme('header');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
    
    // Define body class based on layout style
    $bodyClass = "page-template layout-{$layoutStyle}";
    if ($hasSidebar) {
        $bodyClass .= " has-sidebar sidebar-{$sidebarPosition}";
    }
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale" :bodyClass="$bodyClass">
    <x-dynamic-component :component="$headerComponent" :locale="$locale" />
    
    <main class="site-main">
        <div class="container">
            @if($layoutStyle === 'standard' && !$hasSidebar)
                <article class="page-content">
                    {{ $slot }}
                </article>
            @elseif($hasSidebar)
                <div class="content-with-sidebar">
                    @if($sidebarPosition === 'left')
                        <aside class="sidebar">
                            {{ $sidebar ?? '' }}
                        </aside>
                    @endif
                    
                    <div class="main-content">
                        {{ $slot }}
                    </div>
                    
                    @if($sidebarPosition === 'right')
                        <aside class="sidebar">
                            {{ $sidebar ?? '' }}
                        </aside>
                    @endif
                </div>
            @elseif($layoutStyle === 'full-width')
                <div class="full-width-content">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </main>
    
    <x-dynamic-component :component="$footerComponent" :locale="$locale" />
</x-dynamic-component>
```

## Nested Layouts

Create modular layouts with nested components:

```php
<!-- resources/views/components/inspirecms/your-theme/sections/hero.blade.php -->
@props(['title', 'subtitle' => null, 'backgroundImage' => null, 'buttonText' => null, 'buttonUrl' => null])

<section class="hero-section" @if($backgroundImage) style="background-image: url('{{ $backgroundImage }}')" @endif>
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">{{ $title }}</h1>
            
            @if($subtitle)
                <p class="hero-subtitle">{{ $subtitle }}</p>
            @endif
            
            @if($buttonText && $buttonUrl)
                <a href="{{ $buttonUrl }}" class="hero-button">{{ $buttonText }}</a>
            @endif
            
            {{ $slot }}
        </div>
    </div>
</section>
```

```php
<!-- resources/views/components/inspirecms/your-theme/home.blade.php -->
@props(['content', 'locale' => null])

@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();
    
    $headerComponent = inspirecms_templates()->getComponentWithTheme('header');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
    $heroComponent = inspirecms_templates()->getComponentWithTheme('sections.hero');
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale" bodyClass="home-template">
    <x-dynamic-component :component="$headerComponent" :locale="$locale" />
    
    @propertyNotEmpty('hero', 'title')
        <x-dynamic-component 
            :component="$heroComponent" 
            :title="$hero_title"
            :subtitle="$hero_subtitle ?? null"
            :backgroundImage="$hero_background_image?->getUrl() ?? null"
            :buttonText="$hero_button_text ?? null"
            :buttonUrl="$hero_button_url ?? null"
        />
    @endif
    
    <main class="site-main">
        {{ $slot }}
    </main>
    
    <x-dynamic-component :component="$footerComponent" :locale="$locale" />
</x-dynamic-component>
```

## Layout Sections

Define reusable layout sections:

```php
<!-- resources/views/components/inspirecms/your-theme/sections/cta.blade.php -->
@props(['title', 'content', 'buttonText' => null, 'buttonUrl' => '#', 'backgroundColor' => 'primary'])

<section class="cta-section bg-{{ $backgroundColor }}">
    <div class="container">
        <h2 class="cta-title">{{ $title }}</h2>
        <div class="cta-content">{{ $content }}</div>
        
        @if($buttonText)
            <div class="cta-action">
                <a href="{{ $buttonUrl }}" class="btn btn-{{ $backgroundColor === 'primary' ? 'light' : 'primary' }}">
                    {{ $buttonText }}
                </a>
            </div>
        @endif
    </div>
</section>
```

Use the section in your layouts:

```php
@propertyNotEmpty('cta', 'title')
    <x-inspirecms-your-theme::sections.cta
        :title="$cta_title"
        :content="$cta_content"
        :buttonText="$cta_button_text ?? null"
        :buttonUrl="$cta_button_url ?? '#'"
        :backgroundColor="$cta_background_color ?? 'primary'"
    />
@endif
```

## Layout Best Practices

1. **Consistency**: Maintain consistent structure and naming across layouts
2. **Modularity**: Break layouts into reusable components
3. **Responsiveness**: Ensure layouts work on all device sizes
4. **Accessibility**: Follow WCAG guidelines for accessible layouts
5. **Performance**: Optimize layouts for fast loading
6. **Maintainability**: Use clear organization and comments
7. **Fallbacks**: Provide sensible defaults for missing content
8. **Flexibility**: Design layouts that adapt to different content needs
9. **Semantic HTML**: Use appropriate HTML5 semantic elements
10. **CSS Management**: Keep layout CSS organized and maintainable

## Troubleshooting Layout Issues

### Component Not Found

If you see a "Component not found" error:

1. Verify the component exists in the correct location
2. Check that the component name is correctly spelled
3. Make sure the theme name matches exactly
4. Try clearing the view cache: `php artisan view:clear`

### Layout Not Applying Styles

If your layout is not applying styles:

1. Check that the CSS files exist and are referenced correctly
2. Verify that asset paths are correct
3. Make sure the theme configuration includes the assets
4. Check for CSS conflicts or overrides
5. Inspect the rendered HTML to see if classes are applied

### Layout Breaks on Mobile

If your layout has responsive issues:

1. Add proper viewport meta tag
2. Use responsive CSS (flexbox, grid, media queries)
3. Test on multiple device sizes
4. Avoid fixed widths and use relative units
5. Implement a mobile-first approach

### Content Not Showing

If content is not displaying:

1. Verify content exists and has values
2. Check property names and field groups match
3. Add conditionals to handle missing content
4. Check for template/component mismatches
5. Debug with temporary output of variable values