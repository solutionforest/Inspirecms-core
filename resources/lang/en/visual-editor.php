<?php

return [
    'title' => 'Visual Editor',
    'description' => 'Build beautiful page layouts with drag and drop',

    'categories' => [
        'layout' => 'Layout',
        'basic' => 'Basic',
        'media' => 'Media',
        'interactive' => 'Interactive',
        'integration' => 'Integration',
        'advanced' => 'Advanced',
    ],

    'blocks' => [
        'container' => [
            'label' => 'Container',
            'description' => 'A flexible container for grouping blocks',
        ],
        'section' => [
            'label' => 'Section',
            'description' => 'A page section with background',
        ],
        'grid' => [
            'label' => 'Grid',
            'description' => 'CSS Grid layout',
        ],
        'column' => [
            'label' => 'Column',
            'description' => 'Grid or flex column',
        ],
        'spacer' => [
            'label' => 'Spacer',
            'description' => 'Vertical spacing',
        ],
        'divider' => [
            'label' => 'Divider',
            'description' => 'Horizontal line',
        ],
        'heading' => [
            'label' => 'Heading',
            'description' => 'H1-H6 heading text',
        ],
        'text' => [
            'label' => 'Text',
            'description' => 'Paragraph or rich text',
        ],
        'button' => [
            'label' => 'Button',
            'description' => 'Clickable button or link',
        ],
        'image' => [
            'label' => 'Image',
            'description' => 'Image with optional caption',
        ],
    ],

    'actions' => [
        'add_before' => 'Add Block Before',
        'add_after' => 'Add Block After',
        'add_child' => 'Add Child Block',
        'duplicate' => 'Duplicate',
        'copy' => 'Copy',
        'paste' => 'Paste',
        'cut' => 'Cut',
        'delete' => 'Delete',
        'move_up' => 'Move Up',
        'move_down' => 'Move Down',
        'wrap' => 'Wrap in Container',
        'unwrap' => 'Unwrap',
    ],

    'toolbar' => [
        'undo' => 'Undo',
        'redo' => 'Redo',
        'save' => 'Save',
        'preview' => 'Preview',
        'desktop' => 'Desktop',
        'tablet' => 'Tablet',
        'mobile' => 'Mobile',
    ],

    'panels' => [
        'layers' => 'Layers',
        'blocks' => 'Blocks',
        'settings' => 'Settings',
        'design' => 'Design',
        'ai' => 'AI Assistant',
    ],

    'ai' => [
        'title' => 'AI Assistant',
        'description' => 'Generate layouts with AI',
        'generate' => 'Generate',
        'suggest' => 'Suggest',
        'prompt_placeholder' => 'Describe the layout you want to create...',
        'generating' => 'Generating...',
        'success' => 'Layout generated successfully',
        'error' => 'Failed to generate layout',
        'no_provider' => 'No AI provider configured',
        'templates' => [
            'landing' => 'Landing Page',
            'about' => 'About Page',
            'contact' => 'Contact Page',
            'blog' => 'Blog Layout',
            'portfolio' => 'Portfolio',
            'product' => 'Product Page',
            'pricing' => 'Pricing Page',
            'features' => 'Features Page',
        ],
        'styles' => [
            'modern' => 'Modern',
            'minimal' => 'Minimal',
            'bold' => 'Bold',
            'elegant' => 'Elegant',
            'playful' => 'Playful',
            'corporate' => 'Corporate',
        ],
    ],

    'messages' => [
        'block_added' => 'Block added',
        'block_deleted' => 'Block deleted',
        'block_duplicated' => 'Block duplicated',
        'block_copied' => 'Block copied',
        'block_pasted' => 'Block pasted',
        'layout_saved' => 'Layout saved',
        'cannot_delete_root' => 'Cannot delete root container',
        'nothing_to_paste' => 'Nothing to paste',
        'no_block_selected' => 'No block selected',
    ],
];
