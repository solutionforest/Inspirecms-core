---
title: Layouts
slug: fe-layouts
path: docs/v1/fe-layouts
uri: /docs/1.x/fe-layouts
heading: Layouts
brief:
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

```blade {title="resources/views/components/inspirecms/abc/header.blade.php"}
<nav>
    @foreach (inspirecms()->getNavigation('main', $locale ?? request()->getLocale()) as $item)
        <a href="{{ $item->getUrl() }}">{{ $item->getTitle() }}</a>
    @endforeach
</nav>
```

```blade {title="resources/views/components/inspirecms/abc/footer.blade.php"}
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

```blade {title="resources/views/components/inspirecms/abc/simple-page.blade.php"}
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

```blade {title="Template: home"}
<x-cms-template :content="$content" type="page">
    Home
</x-cms-template>
```

```blade {title="Template: tnc"}
<x-cms-template :content="$content" type="simple-page">
    TNC Here
</x-cms-template>
```
