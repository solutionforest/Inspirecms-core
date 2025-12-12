# InspireCMS Visual Editor

A powerful visual page builder with AI-powered layout generation for InspireCMS. Build beautiful pages with drag-and-drop blocks, similar to Elementor, Builder.io, and Webflow.

## Features

- **Drag & Drop Editor** - Intuitive visual editor with three-panel layout
- **Block-Based System** - 14+ pre-built blocks (layout, content, utility)
- **AI Layout Generation** - Generate layouts using OpenAI or Anthropic
- **Responsive Preview** - Desktop, tablet, and mobile viewport modes
- **Version History** - Track changes with automatic versioning
- **Block Templates** - Save and reuse block configurations
- **Extensible** - Create custom blocks with ease

## Requirements

- PHP >= 8.2
- Laravel 11.x or 12.x
- Filament 3.3+
- Livewire 3.x

## Installation

### Via Composer

```bash
composer require solution-forest/inspirecms-visual-editor
```

### Publish Configuration

```bash
php artisan vendor:publish --tag="visual-editor-config"
```

### Run Migrations

```bash
php artisan migrate
```

### Publish Assets (Optional)

```bash
php artisan vendor:publish --tag="visual-editor-assets"
```

## Configuration

The configuration file is located at `config/visual-editor.php`:

```php
return [
    // Database table prefix
    'table_prefix' => 'cms_',

    // AI Provider Configuration
    'ai' => [
        'provider' => env('VISUAL_EDITOR_AI_PROVIDER', 'anthropic'),

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
        ],

        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-sonnet-20240229'),
        ],
    ],

    // Register custom blocks
    'blocks' => [
        // \App\Blocks\CustomBlock::class,
    ],
];
```

### Environment Variables

```env
# AI Provider (openai or anthropic)
VISUAL_EDITOR_AI_PROVIDER=anthropic

# OpenAI
OPENAI_API_KEY=your-openai-key
OPENAI_MODEL=gpt-4-turbo-preview

# Anthropic
ANTHROPIC_API_KEY=your-anthropic-key
ANTHROPIC_MODEL=claude-3-sonnet-20240229
```

## Usage

### In Filament Forms

Use the `VisualEditorField` in your Filament resources:

```php
use SolutionForest\InspireCmsVisualEditor\Filament\Forms\Components\VisualEditorField;

public static function form(Form $form): Form
{
    return $form->schema([
        TextInput::make('title'),
        VisualEditorField::make('content')
            ->label('Page Content')
            ->columnSpanFull(),
    ]);
}
```

### Rendering Layouts

#### Using Blade Directive

```blade
{{-- Render a complete layout --}}
@visualLayout($page->content)

{{-- Render a single block --}}
@visualBlock($blockData)
```

#### Using Helper Functions

```php
// Render layout with context
echo render_visual_layout($layoutData, ['page' => $page]);

// Render a single block
echo render_visual_block($blockData);
```

#### Using the Facade

```php
use SolutionForest\InspireCmsVisualEditor\Facades\VisualEditor;

$html = VisualEditor::renderLayout($layoutData);
```

#### Using the Service Directly

```php
use SolutionForest\InspireCmsVisualEditor\Rendering\BlockRenderer;

$renderer = app(BlockRenderer::class);
$html = $renderer->renderLayout($layoutData);
```

### Working with Layouts Programmatically

```php
use SolutionForest\InspireCmsVisualEditor\Models\VisualLayout;

// Create a layout
$layout = VisualLayout::create([
    'name' => 'Homepage',
    'layout_data' => [
        'id' => 'root',
        'type' => 'container',
        'children' => [
            [
                'id' => 'heading_1',
                'type' => 'heading',
                'settings' => ['content' => 'Welcome', 'level' => 1],
            ],
        ],
    ],
]);

// Update layout
$layout->update(['layout_data' => $newData]);

// Get versions
$versions = $layout->versions;
```

## Available Blocks

### Layout Blocks

| Block | Type | Description |
|-------|------|-------------|
| Container | `container` | Full-width wrapper with max-width support |
| Section | `section` | Semantic section with content width options |
| Grid | `grid` | CSS Grid layout with configurable columns |
| Column | `column` | Flexible column for use within grids |

### Content Blocks

| Block | Type | Description |
|-------|------|-------------|
| Heading | `heading` | H1-H6 headings with alignment |
| Text | `text` | Rich text content with formatting |
| Button | `button` | Clickable button/link with variants |
| Image | `image` | Responsive images with captions |
| Video | `video` | YouTube, Vimeo, or self-hosted video |
| Embed | `embed` | External content embeds (iframes) |

### Interactive Blocks

| Block | Type | Description |
|-------|------|-------------|
| Tabs | `tabs` | Tabbed content panels |
| Accordion | `accordion` | Collapsible content sections |

### Utility Blocks

| Block | Type | Description |
|-------|------|-------------|
| Spacer | `spacer` | Vertical spacing with responsive heights |
| Divider | `divider` | Horizontal line separator |

## Creating Custom Blocks

### 1. Create the Block Class

```php
<?php

namespace App\Blocks;

use SolutionForest\InspireCmsVisualEditor\Blocks\Types\AbstractBlock;
use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

class TestimonialBlock extends AbstractBlock
{
    public function getType(): string
    {
        return 'testimonial';
    }

    public function getLabel(): string
    {
        return 'Testimonial';
    }

    public function getCategory(): string
    {
        return BlockCategory::Basic->value;
    }

    public function getIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public function getDescription(): string
    {
        return 'Customer testimonial with quote and author';
    }

    public function getSettingsSchema(): array
    {
        return [
            ['name' => 'quote', 'type' => 'textarea', 'label' => 'Quote'],
            ['name' => 'author', 'type' => 'text', 'label' => 'Author Name'],
            ['name' => 'role', 'type' => 'text', 'label' => 'Author Role'],
            ['name' => 'avatar', 'type' => 'image', 'label' => 'Avatar'],
        ];
    }

    public function getDefaultProps(): array
    {
        return [
            'quote' => '',
            'author' => '',
            'role' => '',
            'avatar' => '',
        ];
    }
}
```

### 2. Create the Blade Template

Create `resources/views/vendor/visual-editor/blocks/testimonial.blade.php`:

```blade
@php
    $quote = $settings['quote'] ?? '';
    $author = $settings['author'] ?? '';
    $role = $settings['role'] ?? '';
    $avatar = $settings['avatar'] ?? '';
@endphp

<blockquote{!! $renderer->buildAttributeString($attributes) !!}>
    <p class="ve-testimonial__quote">{{ $quote }}</p>
    <footer class="ve-testimonial__footer">
        @if($avatar)
            <img src="{{ $avatar }}" alt="{{ $author }}" class="ve-testimonial__avatar">
        @endif
        <cite class="ve-testimonial__author">
            <span class="ve-testimonial__name">{{ $author }}</span>
            @if($role)
                <span class="ve-testimonial__role">{{ $role }}</span>
            @endif
        </cite>
    </footer>
</blockquote>
```

### 3. Register the Block

In `config/visual-editor.php`:

```php
'blocks' => [
    \App\Blocks\TestimonialBlock::class,
],
```

## Block Templates

Save frequently used block configurations as templates:

```php
use SolutionForest\InspireCmsVisualEditor\Models\BlockTemplate;

// Save a template
BlockTemplate::create([
    'name' => 'Hero Section',
    'category' => 'sections',
    'block_data' => [
        'type' => 'section',
        'settings' => ['contentWidth' => 'boxed'],
        'children' => [
            ['type' => 'heading', 'settings' => ['level' => 1]],
            ['type' => 'text'],
            ['type' => 'button', 'settings' => ['variant' => 'primary']],
        ],
    ],
    'is_global' => true,
]);

// Use in editor
$templates = BlockTemplate::where('is_global', true)->get();
```

## AI Layout Generation

Generate layouts using natural language:

```php
use SolutionForest\InspireCmsVisualEditor\AI\Services\LayoutGeneratorService;

$generator = app(LayoutGeneratorService::class);

$layout = $generator->generate(
    'Create a landing page with a hero section, features grid, and contact form'
);
```

## Styling

### Editor Styles

The editor includes comprehensive CSS for the UI. Override with custom styles:

```css
/* Override primary color */
:root {
    --ve-primary: #your-color;
}
```

### Block Styles

Frontend block styles are minimal and customizable:

```css
/* Custom heading styles */
.ve-heading {
    font-family: 'Your Font', sans-serif;
}

/* Custom button variants */
.ve-button--primary {
    background: linear-gradient(to right, #color1, #color2);
}
```

## Events

The visual editor dispatches Livewire events:

```php
// In your Livewire component
protected $listeners = [
    'visual-editor:block-added' => 'onBlockAdded',
    'visual-editor:block-updated' => 'onBlockUpdated',
    'visual-editor:block-removed' => 'onBlockRemoved',
    'visual-editor:layout-saved' => 'onLayoutSaved',
];
```

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + C` | Copy selected block |
| `Ctrl/Cmd + V` | Paste block |
| `Ctrl/Cmd + D` | Duplicate block |
| `Ctrl/Cmd + Z` | Undo |
| `Ctrl/Cmd + Shift + Z` | Redo |
| `Delete/Backspace` | Delete selected block |
| `Escape` | Deselect block |

## Testing

Run the test suite:

```bash
cd packages/inspirecms-visual-editor
composer test
```

Or with coverage:

```bash
composer test -- --coverage
```

## API Reference

### BlockRegistry

```php
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;

// Register blocks
BlockRegistry::register(CustomBlock::class);
BlockRegistry::registerMany([Block1::class, Block2::class]);

// Query blocks
BlockRegistry::get('heading');           // Get by type
BlockRegistry::has('heading');           // Check exists
BlockRegistry::all();                    // Get all
BlockRegistry::byCategory('layout');     // Filter by category
BlockRegistry::containers();             // Get container blocks only
BlockRegistry::groupedByCategory();      // Grouped collection

// Create block data
BlockRegistry::createBlockData('heading', 'custom_id');
BlockRegistry::generateBlockId();

// Validation
BlockRegistry::validateLayout($layoutData);
```

### BlockRenderer

```php
use SolutionForest\InspireCmsVisualEditor\Rendering\BlockRenderer;

$renderer = app(BlockRenderer::class);

// Render
$html = $renderer->renderLayout($layoutData, $context);
$html = $renderer->renderBlock($blockData, $context);
$html = $renderer->renderChildren($children, $context);

// Utilities
$attrString = $renderer->buildAttributeString($attributes);
$clean = $renderer->sanitizeHtml($dirtyHtml);
$escaped = $renderer->escape($text);
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md).

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.

## Credits

- [Solution Forest](https://github.com/solutionforest)
- [All Contributors](../../contributors)
