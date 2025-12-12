<?php

use Livewire\Livewire;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ColumnBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ContainerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\GridBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\HeadingBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\TextBlock;
use SolutionForest\InspireCmsVisualEditor\Livewire\VisualEditor;

beforeEach(function () {
    BlockRegistry::clear();
    BlockRegistry::registerMany([
        ContainerBlock::class,
        HeadingBlock::class,
        TextBlock::class,
        GridBlock::class,
        ColumnBlock::class,
    ]);
});

describe('VisualEditor Livewire Component', function () {
    it('can be rendered', function () {
        Livewire::test(VisualEditor::class)
            ->assertStatus(200);
    });

    it('initializes with empty layout', function () {
        Livewire::test(VisualEditor::class)
            ->assertSet('layoutData', []);
    });

    it('can initialize with existing layout data', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'children' => [],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->assertSet('layoutData', $layout);
    });

    it('can add a block', function () {
        Livewire::test(VisualEditor::class)
            ->call('addBlock', 'heading', null, 'inside')
            ->assertSet('selectedBlockId', fn ($value) => str_starts_with($value, 'block_'));
    });

    it('can add a block to a container', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('addBlock', 'heading', 'root', 'inside')
            ->assertSet('layoutData.children', fn ($children) => count($children) === 1);
    });

    it('can select a block', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                [
                    'id' => 'block_1',
                    'type' => 'heading',
                    'settings' => ['content' => 'Test'],
                    'styles' => [],
                    'children' => [],
                ],
            ],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('selectBlock', 'block_1')
            ->assertSet('selectedBlockId', 'block_1');
    });

    it('can deselect a block', function () {
        Livewire::test(VisualEditor::class)
            ->set('selectedBlockId', 'block_1')
            ->call('selectBlock', null)
            ->assertSet('selectedBlockId', null);
    });

    it('can delete a block', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                [
                    'id' => 'block_1',
                    'type' => 'heading',
                    'settings' => ['content' => 'Test'],
                    'styles' => [],
                    'children' => [],
                ],
            ],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('deleteBlock', 'block_1')
            ->assertSet('layoutData.children', []);
    });

    it('can duplicate a block', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                [
                    'id' => 'block_1',
                    'type' => 'heading',
                    'settings' => ['content' => 'Original'],
                    'styles' => [],
                    'children' => [],
                ],
            ],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('duplicateBlock', 'block_1')
            ->assertSet('layoutData.children', fn ($children) => count($children) === 2);
    });

    it('can update block settings', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                [
                    'id' => 'block_1',
                    'type' => 'heading',
                    'settings' => ['content' => 'Original'],
                    'styles' => [],
                    'children' => [],
                ],
            ],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('selectBlock', 'block_1')
            ->call('updateBlockSettings', 'block_1', ['content' => 'Updated'])
            ->assertSet('layoutData.children.0.settings.content', 'Updated');
    });

    it('can update block styles', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                [
                    'id' => 'block_1',
                    'type' => 'heading',
                    'settings' => ['content' => 'Test'],
                    'styles' => [],
                    'children' => [],
                ],
            ],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('updateBlockStyles', 'block_1', ['color' => '#ff0000'])
            ->assertSet('layoutData.children.0.styles.color', '#ff0000');
    });

    it('can move block up', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                ['id' => 'block_1', 'type' => 'heading', 'settings' => [], 'styles' => [], 'children' => []],
                ['id' => 'block_2', 'type' => 'text', 'settings' => [], 'styles' => [], 'children' => []],
            ],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('moveBlockUp', 'block_2')
            ->assertSet('layoutData.children.0.id', 'block_2')
            ->assertSet('layoutData.children.1.id', 'block_1');
    });

    it('can move block down', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                ['id' => 'block_1', 'type' => 'heading', 'settings' => [], 'styles' => [], 'children' => []],
                ['id' => 'block_2', 'type' => 'text', 'settings' => [], 'styles' => [], 'children' => []],
            ],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('moveBlockDown', 'block_1')
            ->assertSet('layoutData.children.0.id', 'block_2')
            ->assertSet('layoutData.children.1.id', 'block_1');
    });

    it('tracks undo history', function () {
        Livewire::test(VisualEditor::class)
            ->call('addBlock', 'heading', null, 'inside')
            ->assertSet('canUndo', true);
    });

    it('can undo changes', function () {
        Livewire::test(VisualEditor::class)
            ->call('addBlock', 'heading', null, 'inside')
            ->call('undo')
            ->assertSet('layoutData', []);
    });

    it('can redo changes', function () {
        Livewire::test(VisualEditor::class)
            ->call('addBlock', 'heading', null, 'inside')
            ->call('undo')
            ->call('redo')
            ->assertSet('layoutData', fn ($data) => ! empty($data));
    });

    it('dispatches events on block operations', function () {
        Livewire::test(VisualEditor::class)
            ->call('addBlock', 'heading', null, 'inside')
            ->assertDispatched('blockAdded');
    });
});

describe('VisualEditor with nested blocks', function () {
    it('can add blocks to nested containers', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                [
                    'id' => 'grid_1',
                    'type' => 'grid',
                    'settings' => ['columns' => 2],
                    'styles' => [],
                    'children' => [
                        [
                            'id' => 'col_1',
                            'type' => 'column',
                            'settings' => [],
                            'styles' => [],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('addBlock', 'heading', 'col_1', 'inside')
            ->assertSet(
                'layoutData.children.0.children.0.children',
                fn ($children) => count($children) === 1 && $children[0]['type'] === 'heading'
            );
    });

    it('can delete nested blocks', function () {
        $layout = [
            'id' => 'root',
            'type' => 'container',
            'settings' => [],
            'styles' => [],
            'children' => [
                [
                    'id' => 'grid_1',
                    'type' => 'grid',
                    'settings' => [],
                    'styles' => [],
                    'children' => [
                        [
                            'id' => 'col_1',
                            'type' => 'column',
                            'settings' => [],
                            'styles' => [],
                            'children' => [
                                [
                                    'id' => 'heading_1',
                                    'type' => 'heading',
                                    'settings' => [],
                                    'styles' => [],
                                    'children' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        Livewire::test(VisualEditor::class, ['layoutData' => $layout])
            ->call('deleteBlock', 'heading_1')
            ->assertSet('layoutData.children.0.children.0.children', []);
    });
});
