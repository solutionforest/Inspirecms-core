# Components

InspireCMS offers a powerful component system based on Laravel's Blade components to help you build modular, reusable interface elements. This guide explains how to use, create, and extend components within InspireCMS.

## Component System Overview

Components in InspireCMS serve as the building blocks of your frontend templates. They:

- Encapsulate reusable interface elements
- Allow passing parameters and content
- Support theme-specific implementations
- Enable advanced template composition
- Improve code organization and maintainability

## Component Types

InspireCMS supports several types of components:

### 1. Theme Components { .font-bold  .text-2xl .my-2 }

Theme-specific components that define your site's visual elements:

- Layout components (header, footer, sidebar)
- Content display components (cards, tabs, modals)
- Navigation components (menus, breadcrumbs)
- Media display components (galleries, sliders)

### 2. Content Components { .font-bold  .text-2xl .my-2 }

Components that render specific types of content:

- Blog post components
- Event listing components
- Product display components
- Team member components

### 3. Utility Components { .font-bold  .text-2xl .my-2 }

Helper components for common UI patterns:

- Pagination components
- Alert/notification components
- Form input components
- Loading indicators

## Component Directory Structure

InspireCMS organizes components in a hierarchical structure:

```
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

## Using Components

### Basic Component Usage { .font-bold  .text-2xl .my-2 }

Using a component in your templates:

```php
<x-cms-theme-component theme="my-theme" name="hero" :title="$heroTitle" :image="$heroImage">
    <p>{{ $heroContent }}</p>
</x-cms-theme-component>
```

### Using Components via Helper { .font-bold  .text-2xl .my-2 }

The component helper makes it easier to use theme-aware components:

```php
@php
$heroComponent = inspirecms_templates()->getComponentWithTheme('hero');
@endphp

<x-dynamic-component :component="$heroComponent" :title="$heroTitle" :image="$heroImage">
    <p>{{ $heroContent }}</p>
</x-dynamic-component>
```

### Component Slots { .font-bold  .text-2xl .my-2 }

Use named slots to organize content within components:

```php
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

## Creating Components

### Basic Component Creation { .font-bold  .text-2xl .my-2 }

Create a simple component:

1. Create the Blade file in your theme directory:

```php
<!-- resources/views/components/inspirecms/my-theme/alert.blade.php -->
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

2. Use your component:

```php
<x-inspirecms-my-theme::alert type="warning" :dismissible="true">
    This is a warning message that can be dismissed.
</x-inspirecms-my-theme::alert>
```

### Component with Props { .font-bold  .text-2xl .my-2 }

Create a component with defined props:

```php
<!-- resources/views/components/inspirecms/my-theme/button.blade.php -->
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

```php
<x-inspirecms-my-theme::button 
    type="success" 
    size="lg" 
    url="#" 
    icon="check"
    class="my-4"
>
    Submit Form
</x-inspirecms-my-theme::button>
```

### Class-Based Components { .font-bold  .text-2xl .my-2 }

For more complex components, create a class-based component:

1. Generate the component class:

```bash
php artisan make:component Gallery --view
```

2. Edit the component class:

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
            // If it's just a string/ID, fetch the media asset
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
            
            // If it's already an array, ensure it has required fields
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

3. Create the view:

```php
<!-- resources/views/components/gallery.blade.php -->
<div class="gallery-container">
    <div class="row">
        @foreach($images as $image)
            <div class="{{ $columnClass() }} gallery-item">
                <figure>
                    @if($lightbox)
                        <a href="{{ $image['url'] }}" class="lightbox-trigger" data-caption="{{ $image['caption'] }}">
                            <img src="{{ $image['thumbnail'] }}" alt="{{ $image['alt'] }}" class="img-fluid">
                        </a>
                    @else
                        <img src="{{ $image['thumbnail'] }}" alt="{{ $image['alt'] }}" class="img-fluid">
                    @endif
                    
                    @if($image['caption'])
                        <figcaption>{{ $image['caption'] }}</figcaption>
                    @endif
                </figure>
            </div>
        @endforeach
    </div>
</div>

@if($lightbox)
    @once
        @push('styles')
            <link href="{{ asset('css/lightbox.css') }}" rel="stylesheet">
        @endpush
        
        @push('scripts')
            <script src="{{ asset('js/lightbox.js') }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Initialize lightbox
                    lightbox.init();
                });
            </script>
        @endpush
    @endonce
@endif
```

4. Use your class-based component:

```php
@propertyArray('page', 'gallery_images')
<x-gallery :images="$page_gallery_images" :columns="4" :lightbox="true" />
```

## Theme-Specific Components

Create components that adapt to the current theme:

### Theme-Aware Components { .font-bold  .text-2xl .my-2 }

Use the theme helper to get the appropriate component for the current theme:

```php
@php
// This will check for 'header' component in the current theme,
// falling back to the default theme if not found
$headerComponent = inspirecms_templates()->getComponentWithTheme('header');
@endphp

<x-dynamic-component :component="$headerComponent" :title="$pageTitle" />
```

### Component Overriding { .font-bold  .text-2xl .my-2 }

Override default components by creating a component with the same name in your theme:

1. Default component:

```php
<!-- resources/views/components/inspirecms/default/navigation.blade.php -->
<nav class="default-navigation">
    <!-- Default navigation implementation -->
</nav>
```

2. Override in your theme:

```php
<!-- resources/views/components/inspirecms/my-theme/navigation.blade.php -->
<nav class="custom-navigation">
    <!-- Custom navigation implementation -->
</nav>
```

The system will automatically use your theme's version when using:

```php
@php
$navComponent = inspirecms_templates()->getComponentWithTheme('navigation');
@endphp

<x-dynamic-component :component="$navComponent" />
```

## Advanced Component Techniques

### Component Composition { .font-bold  .text-2xl .my-2 }

Build complex components by combining smaller ones:

```php
<!-- resources/views/components/inspirecms/my-theme/content-block.blade.php -->
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

### Component Variants { .font-bold  .text-2xl .my-2 }

Create variants of components using attributes:

```php
<!-- resources/views/components/inspirecms/my-theme/card.blade.php -->
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
    @if(isset($image))
        <div class="card-image">
            {{ $image }}
        </div>
    @endif
    
    <div class="card-body">
        @if(isset($title))
            <h3 class="card-title">{{ $title }}</h3>
        @endif
        
        <div class="card-content">
            {{ $slot }}
        </div>
    </div>
    
    @if(isset($footer))
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
```

Use different variants:

```php
<x-inspirecms-my-theme::card variant="feature">
    <x-slot:title>Featured Content</x-slot>
    <p>This is featured content in a special card.</p>
</x-inspirecms-my-theme::card>

<x-inspirecms-my-theme::card variant="outline">
    <x-slot:title>Outlined Card</x-slot>
    <p>This card has an outline style.</p>
</x-inspirecms-my-theme::card>
```

### Dynamic Components { .font-bold  .text-2xl .my-2 }

Create components that render differently based on input:

```php
<!-- resources/views/components/inspirecms/my-theme/content-display.blade.php -->
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

### Component Collections { .font-bold  .text-2xl .my-2 }

Group related components together:

```php
<!-- resources/views/components/inspirecms/my-theme/form/input.blade.php -->
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

<!-- resources/views/components/inspirecms/my-theme/form/textarea.blade.php -->
@props(['name', 'label', 'rows' => 3])

<div class="form-group">
    <label for="{{ $name }}">{{ $label }}</label>
    <textarea 
        id="{{ $name }}" 
        name="{{ $name }}" 
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'form-control']) }}
    >{{ $slot }}</textarea>
    @error($name)
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>

<!-- resources/views/components/inspirecms/my-theme/form/select.blade.php -->
@props(['name', 'label', 'options' => []])

<div class="form-group">
    <label for="{{ $name }}">{{ $label }}</label>
    <select 
        id="{{ $name }}" 
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'form-select']) }}
    >
        @foreach($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
    @error($name)
        <span class="error-message">{{ $message }}</span>
    @enderror
</div>
```

Use component collections:

```php
<form method="post" action="{{ $formAction }}">
    @csrf
    
    <x-inspirecms-my-theme::form.input 
        name="name" 
        label="Your Name" 
        required 
    />
    
    <x-inspirecms-my-theme::form.input 
        name="email" 
        label="Email Address" 
        type="email" 
        required 
    />
    
    <x-inspirecms-my-theme::form.textarea 
        name="message" 
        label="Your Message" 
        rows="5"
        placeholder="Type your message here..."
    />
    
    <x-inspirecms-my-theme::form.select 
        name="subject" 
        label="Subject" 
        :options="['general' => 'General Inquiry', 'support' => 'Support Request', 'feedback' => 'Feedback']"
    />
    
    <x-inspirecms-my-theme::button type="primary">Submit</x-inspirecms-my-theme::button>
</form>
```

## Working with Content in Components

### Content-Aware Components { .font-bold  .text-2xl .my-2 }

Create components that intelligently display content:

```php
<!-- resources/views/components/inspirecms/my-theme/content-renderer.blade.php -->
@props(['content', 'format' => 'full'])

@php
    $documentType = $content->getDocumentType();
    $documentTypeName = $documentType ? $documentType->slug : 'default';
    
    // Try to find a specialized component for this document type
    $componentName = "inspirecms-my-theme::content.{$documentTypeName}-{$format}";
    
    // Check if the specialized component exists
    if (!View::exists("components.{$componentName}")) {
        // Fall back to generic component
        $componentName = "inspirecms-my-theme::content.generic-{$format}";
        
        // If even that doesn't exist, use a very basic fallback
        if (!View::exists("components.{$componentName}")) {
            $componentName = "inspirecms-my-theme::content.fallback";
        }
    }
@endphp

<x-dynamic-component :component="$componentName" :content="$content" />
```

Document-type specific component:

```php
<!-- resources/views/components/inspirecms/my-theme/content/blog-full.blade.php -->
@props(['content'])

<article class="blog-post">
    <header class="blog-header">
        <h1>{{ $content->getTitle() }}</h1>
        
        <div class="meta">
            <time datetime="{{ $content->published_at->format('Y-m-d') }}">
                {{ $content->published_at->format('F j, Y') }}
            </time>
            
            @propertyNotEmpty('blog', 'author')
                <span class="author">by {{ $blog_author }}</span>
            @endif
        </div>
    </header>
    
    @propertyNotEmpty('blog', 'featured_image')
        <div class="featured-image">
            <img 
                src="{{ $blog_featured_image->getUrl() }}" 
                alt="{{ $blog_featured_image->alt_text ?? $content->getTitle() }}"
                class="img-fluid"
            >
        </div>
    @endif
    
    <div class="blog-content">
        @property('blog', 'content')
    </div>
    
    <footer class="blog-footer">
        @propertyArray('blog', 'tags')
            <div class="tags">
                @foreach($blog_tags as $tag)
                    <span class="tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
        
        @propertyNotEmpty('blog', 'show_sharing')
            <div class="share-buttons">
                <x-inspirecms-my-theme::social-share :url="$content->getUrl()" :title="$content->getTitle()" />
            </div>
        @endif
    </footer>
</article>
```

### Content Collection Components { .font-bold  .text-2xl .my-2 }

Components for displaying multiple content items:

```php
<!-- resources/views/components/inspirecms/my-theme/content-grid.blade.php -->
@props([
    'contents', 
    'columns' => 3,
    'mode' => 'card'
])

<div class="content-grid">
    <div class="row">
        @foreach($contents as $content)
            <div class="col-md-{{ 12 / $columns }}">
                <x-inspirecms-my-theme::content-renderer :content="$content" :format="$mode" />
            </div>
        @endforeach
    </div>
    
    @if($contents instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div class="pagination-container">
            {{ $contents->links() }}
        </div>
    @endif
</div>
```

## Testing Components

Create tests to verify component functionality:

```php
namespace Tests\Components;

use Tests\TestCase;
use SolutionForest\InspireCms\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentRendererTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_renders_blog_post_component_for_blog_content()
    {
        // Create a blog post content item
        $blog = Content::factory()->create([
            'title' => json_encode(['en' => 'Test Blog Post']),
            'document_type_id' => 'blog', // assuming blog document type exists
        ]);
        
        // Add properties to the blog post
        $blog->setPropertyValue('blog', 'content', '<p>This is a test blog post content.</p>');
        $blog->setPropertyValue('blog', 'author', 'Test Author');
        
        // Render the component
        $view = $this->component('inspirecms-my-theme::content-renderer', [
            'content' => $blog,
            'format' => 'full',
        ]);
        
        // Assert the component renders correctly
        $view->assertSee('Test Blog Post');
        $view->assertSee('This is a test blog post content.');
        $view->assertSee('Test Author');
    }
}
```

## Best Practices

1. **Keep Components Focused**: Each component should have a single responsibility
2. **Document Components**: Add docblocks and examples to component files
3. **Use Meaningful Props**: Give props clear, descriptive names
4. **Set Default Values**: Provide sensible defaults for component props
5. **Handle Edge Cases**: Anticipate and handle empty or unexpected data
6. **Follow Naming Conventions**: Use consistent naming for components and props
7. **Test Components**: Write tests to verify component rendering
8. **Optimize Component Logic**: Keep performance in mind, especially for repeated components
9. **Use CSS Frameworks Consistently**: Follow a consistent approach to styling
10. **Create Component Libraries**: Group related components into logical collections