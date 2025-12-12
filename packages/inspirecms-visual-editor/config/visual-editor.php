<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Visual Editor Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the visual page builder with AI-powered layout generation.
    |
    */

    /**
     * Whether the visual editor feature is enabled
     */
    'enabled' => true,

    /**
     * AI Provider Configuration
     *
     * Configure AI providers for layout generation.
     * Supported providers: 'openai', 'anthropic'
     */
    'ai' => [
        'provider' => env('INSPIRECMS_AI_PROVIDER', 'anthropic'),

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
        ],

        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-sonnet-20240229'),
        ],
    ],

    /**
     * Database table prefix
     *
     * Prefix for all visual editor database tables.
     * Set to null to use no prefix.
     */
    'table_prefix' => 'cms_',

    /**
     * Additional custom block types to register
     *
     * Add your custom block classes here.
     * Each class must implement BlockInterface.
     */
    'blocks' => [
        // \App\VisualEditor\Blocks\CustomBlock::class,
    ],

    /**
     * Block templates storage
     */
    'block_templates' => [
        'disk' => 'public',
        'directory' => 'visual-editor/templates',
    ],

    /**
     * Media storage for visual editor
     */
    'media' => [
        'disk' => 'public',
        'directory' => 'visual-editor',
    ],

    /**
     * History settings
     */
    'history' => [
        'max_undo_steps' => 50,
        'auto_save_interval' => 30, // seconds
    ],

    /**
     * Preview settings
     */
    'preview' => [
        'breakpoints' => [
            'desktop' => 1200,
            'tablet' => 768,
            'mobile' => 375,
        ],
    ],

];
