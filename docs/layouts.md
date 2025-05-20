---
title: Layouts
slug: layouts
path: docs/v1/layouts
uri: /docs/1.x/layouts
---
# Layouts

Learn how to create and use layouts in InspireCMS to build consistent page structures.

---

## Overview

Layouts in InspireCMS define the overall structure of your pages, allowing you to maintain a consistent look and feel across your site. InspireCMS supports two approaches to layouts:

1. **Component-Based Layouts**: Using Blade components
2. **Template Inheritance**: Using Blade's `@extends` directive

---

## Component-Based Layouts

Component-based layouts use Blade components to build the page structure.

### Creating a Base Layout Component

```blade {title="resources/views/components/inspirecms/your-theme/layout.blade.php"}
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

    <!-- Styles -->
    @php
        $themeConfig = config('themes.' . inspirecms_templates()->getCurrentTheme());
        $themeAssets = $themeConfig['assets'] ?? [];
    @endphp

    @foreach($themeAssets['css'] ?? [] as $css)
        <link rel="stylesheet" href="{{ asset($css) }}?v={{ config('inspirecms.version', time()) }}">
    @endforeach

    @stack('styles')
</head>
<body class="{{ $attributes->get('bodyClass', '') }}">
    <!-- Content -->
    {{ $slot }}

    <!-- JavaScript -->
    @foreach($themeAssets['js'] ?? [] as $js)
        <script src="{{ asset($js) }}?v={{ config('inspirecms.version', time()) }}" defer></script>
    @endforeach

    @stack('scripts')
</body>
</html>
```

### Header and Footer Components

Create header and footer components to use with your layout:

```blade {title="resources/views/components/inspirecms/your-theme/header.blade.php"}
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
        </div>
    </div>
</header>
```

> [!Note]
> For additional component examples and best practices, see the [Components](./vire-components){.doc-link} documentation.

### Content Layout Component

```blade {title="resources/views/components/inspirecms/your-theme/page.blade.php"}
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

---

## Template Inheritance

Template inheritance uses Blade's `@extends`, `@section`, and `@yield` directives to build layouts.

### Creating a Master Layout

```blade {title="resources/views/layouts/inspirecms/your-theme/master.blade.php"}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta -->
    @yield('seo')

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

### Creating Page Templates

```blade {title="resources/views/templates/inspirecms/your-theme/page.blade.php"}
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

> ![Note]
> For more detailed examples of using templates, see the [Templates](./templates){.doc-link} documentation.

---

## Specialized Layouts

Create specialized layouts for different content types:

### Blog Layout

```blade {title="resources/views/components/inspirecms/your-theme/blog-layout.blade.php"
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

---

## Handling Multi-Language Layouts

Adapt layouts for multiple languages:

```blade {title="resources/views/components/inspirecms/your-theme/language-switcher.blade.php"}
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

### RTL Support

Add support for right-to-left languages:

```blade {title="resources/views/components/inspirecms/your-theme/layout.blade.php"}
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
        <link rel="stylesheet" href="{{ asset('css/rtl.css') }}">
    @endif
</head>
<body class="{{ $attributes->get('bodyClass', '') }} {{ $direction }}">
    <!-- ... body content ... -->
</body>
</html>
```

---

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
