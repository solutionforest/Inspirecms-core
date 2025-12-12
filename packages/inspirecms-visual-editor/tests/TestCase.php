<?php

namespace SolutionForest\InspireCmsVisualEditor\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use SolutionForest\InspireCmsVisualEditor\VisualEditorServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            VisualEditorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set up test database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Visual editor config
        $app['config']->set('visual-editor.table_prefix', 'test_');
        $app['config']->set('visual-editor.ai.provider', 'anthropic');
    }

    /**
     * Create a sample block data structure
     */
    protected function createSampleBlock(string $type, array $settings = [], array $children = []): array
    {
        return [
            'id' => 'block_' . uniqid(),
            'type' => $type,
            'settings' => $settings,
            'styles' => [],
            'children' => $children,
        ];
    }

    /**
     * Create a sample layout structure
     */
    protected function createSampleLayout(): array
    {
        return [
            'id' => 'block_root',
            'type' => 'container',
            'settings' => ['maxWidth' => '1200px'],
            'styles' => [],
            'children' => [
                [
                    'id' => 'block_section1',
                    'type' => 'section',
                    'settings' => ['contentWidth' => 'boxed'],
                    'styles' => ['padding' => '2rem'],
                    'children' => [
                        [
                            'id' => 'block_heading1',
                            'type' => 'heading',
                            'settings' => ['content' => 'Hello World', 'level' => 1],
                            'styles' => [],
                            'children' => [],
                        ],
                        [
                            'id' => 'block_text1',
                            'type' => 'text',
                            'settings' => ['content' => '<p>This is sample text content.</p>'],
                            'styles' => [],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ];
    }
}
