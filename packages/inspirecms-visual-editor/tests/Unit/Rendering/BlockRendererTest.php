<?php

use Illuminate\Support\HtmlString;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ButtonBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ColumnBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ContainerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\DividerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\GridBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\HeadingBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ImageBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\SectionBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\SpacerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\TextBlock;
use SolutionForest\InspireCmsVisualEditor\Rendering\BlockRenderer;

beforeEach(function () {
    BlockRegistry::clear();
    BlockRegistry::registerMany([
        ContainerBlock::class,
        SectionBlock::class,
        GridBlock::class,
        ColumnBlock::class,
        HeadingBlock::class,
        TextBlock::class,
        ButtonBlock::class,
        ImageBlock::class,
        SpacerBlock::class,
        DividerBlock::class,
    ]);

    $this->renderer = new BlockRenderer;
});

describe('BlockRenderer', function () {
    it('renders an empty array as empty string', function () {
        $result = $this->renderer->renderBlock([]);

        expect($result)->toBe('');
    });

    it('renders a simple heading block', function () {
        $block = [
            'id' => 'block_1',
            'type' => 'heading',
            'settings' => [
                'content' => 'Test Heading',
                'level' => 2,
            ],
            'styles' => [],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($block);

        expect($result)->toContain('Test Heading');
        expect($result)->toContain('<h2');
        expect($result)->toContain('ve-heading');
    });

    it('renders a text block with HTML content', function () {
        $block = [
            'id' => 'block_1',
            'type' => 'text',
            'settings' => [
                'content' => '<p>This is <strong>bold</strong> text.</p>',
            ],
            'styles' => [],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($block);

        expect($result)->toContain('ve-text');
        expect($result)->toContain('<p>');
        expect($result)->toContain('<strong>bold</strong>');
    });

    it('renders a button block', function () {
        $block = [
            'id' => 'block_1',
            'type' => 'button',
            'settings' => [
                'text' => 'Click Me',
                'url' => 'https://example.com',
                'variant' => 'primary',
            ],
            'styles' => [],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($block);

        expect($result)->toContain('Click Me');
        expect($result)->toContain('href="https://example.com"');
        expect($result)->toContain('ve-button');
        expect($result)->toContain('ve-button--primary');
    });

    it('renders an image block', function () {
        $block = [
            'id' => 'block_1',
            'type' => 'image',
            'settings' => [
                'src' => '/images/test.jpg',
                'alt' => 'Test image',
            ],
            'styles' => [],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($block);

        expect($result)->toContain('ve-image');
        expect($result)->toContain('src="/images/test.jpg"');
        expect($result)->toContain('alt="Test image"');
    });

    it('renders nested blocks recursively', function () {
        $layout = [
            'id' => 'block_root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                [
                    'id' => 'block_heading',
                    'type' => 'heading',
                    'settings' => ['content' => 'Nested Heading', 'level' => 1],
                    'styles' => [],
                    'children' => [],
                ],
            ],
        ];

        $result = $this->renderer->renderBlock($layout);

        expect($result)->toContain('ve-container');
        expect($result)->toContain('ve-heading');
        expect($result)->toContain('Nested Heading');
    });

    it('renders a complete layout', function () {
        $layout = $this->createSampleLayout();

        $result = $this->renderer->renderLayout($layout);

        expect($result)->toBeInstanceOf(HtmlString::class);
        expect((string) $result)->toContain('ve-container');
        expect((string) $result)->toContain('Hello World');
    });

    it('applies inline styles from styles array', function () {
        $block = [
            'id' => 'block_1',
            'type' => 'heading',
            'settings' => ['content' => 'Styled', 'level' => 2],
            'styles' => [
                'color' => '#ff0000',
                'fontSize' => '24px',
                'marginBottom' => '20px',
            ],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($block);

        expect($result)->toContain('color: #ff0000');
        expect($result)->toContain('font-size: 24px');
        expect($result)->toContain('margin-bottom: 20px');
    });

    it('adds custom CSS class from settings', function () {
        $block = [
            'id' => 'block_1',
            'type' => 'heading',
            'settings' => [
                'content' => 'Custom Class',
                'level' => 2,
                'cssClass' => 'my-custom-class',
            ],
            'styles' => [],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($block);

        expect($result)->toContain('my-custom-class');
    });

    it('handles unknown block types gracefully', function () {
        $block = [
            'id' => 'block_1',
            'type' => 'unknown_block_type',
            'settings' => [],
            'styles' => [],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($block);

        // In debug mode it returns a comment, in production empty string
        expect($result)->toBeString();
    });

    it('prevents infinite recursion with max depth', function () {
        // Create a deeply nested structure
        $block = [
            'id' => 'block_1',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [],
        ];

        $current = &$block;
        for ($i = 0; $i < 60; $i++) {
            $current['children'] = [[
                'id' => 'block_' . ($i + 2),
                'type' => 'container',
                'settings' => [],
                'styles' => [],
                'children' => [],
            ]];
            $current = &$current['children'][0];
        }

        $result = $this->renderer->renderBlock($block);

        expect($result)->toContain('Max nesting depth reached');
    });
});

describe('BlockRenderer utilities', function () {
    it('builds attribute string correctly', function () {
        $attributes = [
            'id' => 'test-id',
            'class' => 'class1 class2',
            'data-value' => 'test',
        ];

        $result = $this->renderer->buildAttributeString($attributes);

        expect($result)->toContain('id="test-id"');
        expect($result)->toContain('class="class1 class2"');
        expect($result)->toContain('data-value="test"');
    });

    it('escapes attribute values', function () {
        $attributes = [
            'data-value' => '<script>alert("xss")</script>',
        ];

        $result = $this->renderer->buildAttributeString($attributes);

        expect($result)->not->toContain('<script>');
        expect($result)->toContain('&lt;script&gt;');
    });

    it('omits null and false attributes', function () {
        $attributes = [
            'id' => 'test',
            'class' => null,
            'disabled' => false,
        ];

        $result = $this->renderer->buildAttributeString($attributes);

        expect($result)->toContain('id="test"');
        expect($result)->not->toContain('class');
        expect($result)->not->toContain('disabled');
    });

    it('sanitizes HTML content', function () {
        $dirty = '<script>alert("xss")</script><p>Safe content</p>';

        $clean = $this->renderer->sanitizeHtml($dirty);

        expect($clean)->not->toContain('<script>');
        expect($clean)->toContain('<p>Safe content</p>');
    });

    it('allows safe HTML tags', function () {
        $html = '<p><strong>Bold</strong> and <em>italic</em></p>';

        $clean = $this->renderer->sanitizeHtml($html);

        expect($clean)->toContain('<strong>Bold</strong>');
        expect($clean)->toContain('<em>italic</em>');
    });

    it('escapes plain text', function () {
        $text = '<script>alert("xss")</script>';

        $escaped = $this->renderer->escape($text);

        expect($escaped)->not->toContain('<script>');
        expect($escaped)->toContain('&lt;script&gt;');
    });
});

describe('BlockRenderer grid and layout', function () {
    it('renders a grid with columns', function () {
        $grid = [
            'id' => 'block_grid',
            'type' => 'grid',
            'settings' => ['columns' => 3, 'gap' => '20px'],
            'styles' => [],
            'children' => [
                [
                    'id' => 'block_col1',
                    'type' => 'column',
                    'settings' => [],
                    'styles' => [],
                    'children' => [],
                ],
                [
                    'id' => 'block_col2',
                    'type' => 'column',
                    'settings' => [],
                    'styles' => [],
                    'children' => [],
                ],
            ],
        ];

        $result = $this->renderer->renderBlock($grid);

        expect($result)->toContain('ve-grid');
        expect($result)->toContain('ve-column');
        expect($result)->toContain('grid-template-columns');
    });

    it('renders a section with content width', function () {
        $section = [
            'id' => 'block_section',
            'type' => 'section',
            'settings' => ['contentWidth' => 'narrow'],
            'styles' => [],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($section);

        expect($result)->toContain('ve-section');
        expect($result)->toContain('ve-section--narrow');
    });

    it('renders a spacer with height', function () {
        $spacer = [
            'id' => 'block_spacer',
            'type' => 'spacer',
            'settings' => ['height' => '50px'],
            'styles' => [],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($spacer);

        expect($result)->toContain('ve-spacer');
        expect($result)->toContain('height: 50px');
    });

    it('renders a divider with style', function () {
        $divider = [
            'id' => 'block_divider',
            'type' => 'divider',
            'settings' => [
                'style' => 'dashed',
                'color' => '#cccccc',
            ],
            'styles' => [],
            'children' => [],
        ];

        $result = $this->renderer->renderBlock($divider);

        expect($result)->toContain('ve-divider');
        expect($result)->toContain('<hr');
    });
});
