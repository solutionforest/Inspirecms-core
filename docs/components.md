---
title: Components
slug: components
path: docs/v1/components
uri: /docs/1.x/components
---

# Components

InspireCMS offers a powerful component system based on Laravel's Blade components to help you build modular, reusable interface elements.

---

## Overview

Components in InspireCMS serve as the building blocks of your frontend templates. They:

-   Encapsulate reusable interface elements
-   Allow passing parameters and content
-   Support theme-specific implementations
-   Enable advanced template composition
-   Improve code organization and maintainability

---

## Component Types

InspireCMS supports several types of components:

### 1. Theme Components

Theme-specific components that define your site's visual elements:

-   Layout components (header, footer, sidebar)
-   Content display components (cards, tabs, modals)
-   Navigation components (menus, breadcrumbs)
-   Media display components (galleries, sliders)

### 2. Content Components

Components that render specific types of content:

-   Blog post components
-   Event listing components
-   Product display components
-   Team member components

### 3. Utility Components

Helper components for common UI patterns:

-   Pagination components
-   Alert/notification components
-   Form input components
-   Loading indicators

## Component Directory Structure

InspireCMS organizes components in a hierarchical structure:

```plaintext
resources/views/components/
├── inspirecms/                   # InspireCMS-specific components
│   ├── default/                  # Default theme components
│   │   ├── layout.blade.php      # Base layout component
│   │   ├── page.blade.php        # Page template component
│   │   └── ...                   # Other default components
│   │
│   └── your-theme/               # Your custom theme components
│       ├── layout.blade.php      # Theme-specific layout
│       ├── header.blade.php      # Theme-specific header
│       └── ...                   # Other theme components
│
└── common/                       # Theme-agnostic shared components
    ├── alert.blade.php
    ├── pagination.blade.php
    └── ...
```

---

## Using Components

### Basic Component Usage

Using a component in your templates:

```blade
<x-cms-theme-component theme="my-theme" name="hero" :title="$heroTitle" :image="$heroImage">
    <p>{{ $heroContent }}</p>
</x-cms-theme-component>
```

### Using Components via Helper

The component helper makes it easier to use theme-aware components:

```blade
<x-dynamic-component :component="inspirecms_templates()->getComponentWithTheme('hero')" :title="$heroTitle" :image="$heroImage">
    <p>{{ $heroContent }}</p>
</x-dynamic-component>
```

### Component Slots

Use named slots to organize content within components:

```blade
<x-inspirecms-my-theme::card class="featured-card">
    <x-slot:header>
        <h3>{{ $title }}</h3>
    </x-slot>

    <p>{{ $content }}</p>

    <x-slot:footer>
        <a href="{{ $url }}" class="btn">Read more</a>
    </x-slot>
</x-inspirecms-my-theme::card>
```

---

## Creating Components

### Basic Component Creation

Create a simple component:

```blade {title="resources/views/components/inspirecms/my-theme/alert.blade.php'}
<div class="alert alert-{{ $type ?? 'info' }}" role="alert">
    @if(isset($dismissible) && $dismissible)
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    @endif

    <div class="alert-content">
        {{ $slot }}
    </div>
</div>
```

Use your component:

```blade
<x-inspirecms-my-theme::alert type="warning" :dismissible="true">
    This is a warning message that can be dismissed.
</x-inspirecms-my-theme::alert>
```

### Component with Props

Create a component with defined props:

```blade {title="resources/views/components/inspirecms/my-theme/button.blade.php"}
@props([
    'type' => 'primary',
    'size' => 'md',
    'url' => null,
    'disabled' => false,
    'icon' => null,
])

@php
    $baseClass = 'btn';
    $classes = [
        $baseClass,
        "btn-{$type}",
        "btn-{$size}",
        $disabled ? 'disabled' : '',
    ];
    $attributes = $attributes->class(implode(' ', array_filter($classes)));
@endphp

@if($url && !$disabled)
    <a href="{{ $url }}" {{ $attributes }}>
        @if($icon)<span class="icon icon-{{ $icon }}"></span>@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'disabled' => $disabled]) }}>
        @if($icon)<span class="icon icon-{{ $icon }}"></span>@endif
        {{ $slot }}
    </button>
@endif
```

Use this component:

```blade
<x-dynamic-component :component="inspirecms_templates()->getComponentWithTheme('button')"
    type="success"
    size="lg"
    url="#"
    icon="check"
    class="my-4"
>
    Submit Form
</x-dynamic-component>
```

### Class-Based Components

For more complex components, create a class-based component:

```php
namespace App\View\Components;

use Illuminate\View\Component;

class Gallery extends Component
{
    public $images;
    public $columns;
    public $lightbox;

    public function __construct($images = [], $columns = 3, $lightbox = true)
    {
        $this->images = $this->processImages($images);
        $this->columns = max(1, min(12, (int) $columns));
        $this->lightbox = $lightbox;
    }

    public function render()
    {
        return view('components.gallery');
    }

    private function processImages($images)
    {
        // Process and normalize image data
        return collect($images)->map(function ($image) {
            if (is_string($image) || is_numeric($image)) {
                $media = inspirecms_asset()->findByKey($image);
                if ($media) {
                    return [
                        'url' => $media->getUrl(),
                        'thumbnail' => $media->getUrl(['width' => 300, 'height' => 200, 'fit' => 'crop']),
                        'alt' => $media->alt_text ?? '',
                        'caption' => $media->caption ?? '',
                        'original' => $media,
                    ];
                }
                return null;
            }

            return [
                'url' => $image['url'] ?? '',
                'thumbnail' => $image['thumbnail'] ?? $image['url'] ?? '',
                'alt' => $image['alt'] ?? '',
                'caption' => $image['caption'] ?? '',
                'original' => $image['original'] ?? null,
            ];
        })->filter();
    }

    public function columnClass()
    {
        return 'col-' . (12 / $this->columns);
    }
}
```

---

## Theme-Specific Components

Create components that adapt to the current theme:

### Theme-Aware Components

Use the theme helper to get the appropriate component for the current theme:

```blade
@php
// This will check for 'header' component in the current theme,
// falling back to the default theme if not found
$headerComponent = inspirecms_templates()->getComponentWithTheme('header');
@endphp

<x-dynamic-component :component="$headerComponent" :title="$pageTitle" />
```

### Component Overriding

Override default components by creating a component with the same name in your theme:

```blade {title="resources/views/components/inspirecms/default/navigation.blade.php"}
<nav class="default-navigation">
    <!-- Default navigation implementation -->
</nav>
```

```blade {title="resources/views/components/inspirecms/my-theme/navigation.blade.php"}
<nav class="custom-navigation">
    <!-- Custom navigation implementation -->
</nav>
```

## Advanced Component Techniques

### Component Composition

Build complex components by combining smaller ones:

```blade {title="resources/views/components/inspirecms/my-theme/content-block.blade.php"}
<section class="content-block {{ $type ?? 'default' }}">
    <div class="container">
        @if($title)
            <x-inspirecms-my-theme::section-title :text="$title" :subtitle="$subtitle" />
        @endif

        <div class="content-block-body">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="content-block-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</section>
```

### Component Variants

Create variants of components using attributes:

```blade {title="resources/views/components/inspirecms/my-theme/card.blade.php"}
@props([
    'variant' => 'default',
    'overlay' => false,
    'aspectRatio' => null,
])

@php
    $variants = [
        'default' => 'card-default',
        'feature' => 'card-feature bg-primary text-white',
        'outline' => 'card-outline border-primary',
        'simple' => 'card-simple shadow-sm',
    ];

    $variantClass = $variants[$variant] ?? $variants['default'];

    $classes = [
        'card',
        $variantClass,
        $overlay ? 'card-overlay' : '',
        $aspectRatio ? "ratio-{$aspectRatio}" : '',
    ];
@endphp

<div {{ $attributes->merge(['class' => implode(' ', array_filter($classes))]) }}>
    <!-- Card content -->
</div>
```

### Dynamic Components

Create components that render differently based on input:

```blade {title="resources/views/components/inspirecms/my-theme/content-display.blade.php"}
@props([
    'content',
    'mode' => 'full',
])

@php
    $modes = [
        'full' => 'components.content.full-display',
        'summary' => 'components.content.summary-display',
        'card' => 'components.content.card-display',
        'minimal' => 'components.content.minimal-display',
    ];

    $view = $modes[$mode] ?? $modes['full'];
@endphp

<x-dynamic-component :component="$view" :content="$content" />
```

### Component Collections

Group related components together:

```blade {title="resources/views/components/inspirecms/my-theme/form/input.blade.php"}
@props(['name', 'label', 'type' => 'text'])

<div class="form-group">
    <label for="{{ $name }}">{{ $label }}</label>
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'form-control']) }}
    >
    @error($name)
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>
```

--

## Working with Content in Components

### Content-Aware Components

Create components that intelligently display content:

```blade {title="resources/views/components/inspirecms/my-theme/content-renderer.blade.php"}
@props(['content', 'format' => 'full'])

@php
    $documentTypeName = $content->documentType : 'default';

    // Try to find a specialized component for this document type
    $componentName = collect(["content.{$documentTypeName}-{$format}", "content.generic-{$format}"])
        // Check if the specialized component exists
        ->where(fn ($name) => inspirecms_template()->hasComponent($name))
        ->map(fn ($name) => inspirecms_templates()->getComponentWithTheme($name))
        // If even that doesn't exist, use a very basic fallback
        ->first() ?? inspirecms_template()->getComponentWithTheme("content.fallback");
@endphp

<x-dynamic-component :component="$componentName" :content="$content" />
```

---

## Best Practices

1. **Keep Components Focused**: Each component should have a single responsibility
2. **Document Components**: Add docblocks and examples to component files
3. **Use Meaningful Props**: Give props clear, descriptive names
4. **Set Default Values**: Provide sensible defaults for component props
5. **Handle Edge Cases**: Anticipate and handle empty or unexpected data
6. **Follow Naming Conventions**: Use consistent naming for components and props
7. **Test Components**: Write tests to verify component rendering

> [!note]
>
> For detailed examples of using components in templates, see the [Templates](./templates){.doc-link} documentation.
>
> For integrating components into layouts, see the [Layouts](./layouts){.doc-link} documentation.
