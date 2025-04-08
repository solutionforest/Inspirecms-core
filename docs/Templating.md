# Templating in InspireCMS

InspireCMS templates allow you to define reusable layouts and components for your content.

## Theme Management

### Creating a Theme
1. Navigate to `/cms/settings/templates`
2. Click `Create theme` or `Clone theme`
3. Enter your theme name and submit
4. New theme components will be placed at `resources/views/components/inspirecms/{new_theme}`

### Changing Themes
1. Navigate to `/cms/settings/templates` 
2. Click `Change theme`
3. Select your desired theme

> [!NOTE]
> You can view the current theme by running `php artisan inspirecms:about`

## Template Creation

1. Navigate to `/cms/settings/document-types`
2. Select your target **Document Type**
3. Create or edit an existing template for that **Document Type**

InspireCMS uses [Blade](https://laravel.com/docs/11.x/blade) and automatically binds `$content`. Example:

```php
@php
    $locale ??= $content->getLocale();
@endphp
<x-cms-template :content="$content" type="page">
    <p>@property('banner', 'title')</p>
    <p>Your content here</p>
</x-cms-template>
```

## Implementation Examples

Let's assume you've created a theme named "abc".

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

## Adding Fields to Template

TBC...