---
title: Layouts
slug: fe-layouts
path: docs/v1/fe-layouts
uri: /docs/v1/fe-layouts
heading: Layouts
brief: 
quick_links: []
---

## Overview

Basically, we are using [Blade components](https://laravel.com/docs/11.x/blade) to create reusable UI elements.

### Creating Theme Components

Create a new component in your theme:

```blade { title="resources/views/components/inspirecms/your-theme/hero.blade.php" }
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

```blade
@php
    $heroComponent = inspirecms_templates()->getComponentWithTheme('hero');
@endphp
<x-dynamic-component :component="$heroComponent" :title="$pageTitle">
    <p>Custom hero content here</p>
</x-dynamic-component>
```

---

## Implementation Examples

Let's assume you've created a theme named "**abc**".

#### Folder Structure

```plaintext
resources/views/components/inspirecms/abc/
├── footer.blade.php
├── header.blade.php
├── layout.blade.php
├── page.blade.php
└── simple-page.blade.php
```

#### Component Files

```blade {title="resources/views/components/inspirecms/abc/layout.blade.php"}
@props(['title' => null, 'seo' => null, 'locale' => null, 'isPreviewing' => false, 'isSimple' => false])
@php
    $title ??= config('app.name');
    $locale ??= request()->getLocale();

    $headerComponent = inspirecms_templates()->getComponentWithTheme('header');
    $footerComponent = inspirecms_templates()->getComponentWithTheme('footer');
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
        <x-dynamic-component :component="$headerComponent" :locale="$locale" />
        {{ $slot }}
        <x-dynamic-component :component="$footerComponent" :locale="$locale" />
        @yield('scripts')
    </body>
</html>
```

```blade {title="resources/views/components/inspirecms/abc/header.blade.php"}
@props(['locale' => null])
@aware(['isPreviewing'])
<nav>
    @foreach (inspirecms()->getNavigation('main', $locale ?? request()->getLocale()) as $item)
        <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
    @endforeach
</nav>
```

```blade {title="resources/views/components/inspirecms/abc/footer.blade.php"}
@props(['locale' => null])
@aware(['isPreviewing'])
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

```blade {title="resources/views/components/inspirecms/abc/page.blade.php"
@props(['content', 'locale' => null])
@aware(['isPeekPreviewModal' => false])
@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();

    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
@endphp
<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale" :isPreviewing="$isPeekPreviewModal">
    {{ $slot }}
</x-dynamic-component>
```

```blade {title="resources/views/components/inspirecms/abc/simple-page.blade.php"}
@props(['content', 'locale' => null])
@aware(['isPeekPreviewModal' => false])
@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();

    $layoutComponent = inspirecms_templates()->getComponentWithTheme('layout');
@endphp
<x-dynamic-component :component="$layoutComponent" :title="$title" :seo="$seo" :locale="$locale" :isPreviewing="$isPeekPreviewModal" :isSimple="true">
    {{ $slot }}
</x-dynamic-component>
```

#### Applying Layouts to Templates

```blade {title="Template: home"}
@props(['content', 'locale' => null, 'isPeekPreviewModal' => false])
@php
    $locale ??= $content->getLocale();

    $layoutComponent = inspirecms_templates()->getComponentWithTheme('page');
@endphp
<x-dynamic-component :component="$layoutComponent" :content="$content" :locale="$locale" :isPeekPreviewModal="$isPeekPreviewModal">
    Home
</x-dynamic-component>
```

```blade {title="Template: tnc"}
@props(['content', 'locale' => null, 'isPeekPreviewModal' => false])
@php
    $locale ??= $content->getLocale();

    $layoutComponent = inspirecms_templates()->getComponentWithTheme('simple-page');
@endphp
<x-dynamic-component :component="$layoutComponent" :content="$content" :locale="$locale" :isPeekPreviewModal="$isPeekPreviewModal">
    TNC Here
</x-dynamic-component>
```
