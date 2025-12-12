<?php

use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ContainerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\HeadingBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\TextBlock;
use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

beforeEach(function () {
    BlockRegistry::clear();
});

describe('BlockRegistry', function () {
    it('can register a block', function () {
        BlockRegistry::register(HeadingBlock::class);

        expect(BlockRegistry::has('heading'))->toBeTrue();
    });

    it('can register multiple blocks', function () {
        BlockRegistry::registerMany([
            ContainerBlock::class,
            HeadingBlock::class,
            TextBlock::class,
        ]);

        expect(BlockRegistry::has('container'))->toBeTrue();
        expect(BlockRegistry::has('heading'))->toBeTrue();
        expect(BlockRegistry::has('text'))->toBeTrue();
    });

    it('can get a registered block by type', function () {
        BlockRegistry::register(HeadingBlock::class);

        $block = BlockRegistry::get('heading');

        expect($block)->toBeInstanceOf(HeadingBlock::class);
        expect($block->getType())->toBe('heading');
    });

    it('returns null for unregistered block type', function () {
        expect(BlockRegistry::get('nonexistent'))->toBeNull();
    });

    it('can get all registered blocks', function () {
        BlockRegistry::registerMany([
            ContainerBlock::class,
            HeadingBlock::class,
        ]);

        $blocks = BlockRegistry::all();

        expect($blocks)->toHaveCount(2);
        expect($blocks->keys()->toArray())->toContain('container', 'heading');
    });

    it('can get blocks by category', function () {
        BlockRegistry::registerMany([
            ContainerBlock::class,
            HeadingBlock::class,
            TextBlock::class,
        ]);

        $layoutBlocks = BlockRegistry::byCategory(BlockCategory::Layout);
        $basicBlocks = BlockRegistry::byCategory(BlockCategory::Basic);

        expect($layoutBlocks)->toHaveCount(1);
        expect($basicBlocks)->toHaveCount(2);
    });

    it('can get blocks grouped by category', function () {
        BlockRegistry::registerMany([
            ContainerBlock::class,
            HeadingBlock::class,
            TextBlock::class,
        ]);

        $grouped = BlockRegistry::groupedByCategory();

        expect($grouped)->toHaveKey('layout');
        expect($grouped)->toHaveKey('basic');
    });

    it('can get container blocks only', function () {
        BlockRegistry::registerMany([
            ContainerBlock::class,
            HeadingBlock::class,
        ]);

        $containers = BlockRegistry::containers();

        expect($containers)->toHaveCount(1);
        expect($containers->first()->getType())->toBe('container');
    });

    it('can clear all registered blocks', function () {
        BlockRegistry::register(HeadingBlock::class);
        expect(BlockRegistry::has('heading'))->toBeTrue();

        BlockRegistry::clear();

        expect(BlockRegistry::has('heading'))->toBeFalse();
        expect(BlockRegistry::all())->toBeEmpty();
    });

    it('can create block data with default props', function () {
        BlockRegistry::register(HeadingBlock::class);

        $blockData = BlockRegistry::createBlockData('heading');

        expect($blockData)->toBeArray();
        expect($blockData)->toHaveKeys(['id', 'type', 'props', 'styles', 'children']);
        expect($blockData['type'])->toBe('heading');
        expect($blockData['id'])->toStartWith('block_');
    });

    it('can create block data with custom id', function () {
        BlockRegistry::register(HeadingBlock::class);

        $blockData = BlockRegistry::createBlockData('heading', 'my_custom_id');

        expect($blockData['id'])->toBe('my_custom_id');
    });

    it('returns null when creating data for unregistered block', function () {
        expect(BlockRegistry::createBlockData('nonexistent'))->toBeNull();
    });

    it('can get blocks for panel display', function () {
        BlockRegistry::registerMany([
            ContainerBlock::class,
            HeadingBlock::class,
        ]);

        $panelBlocks = BlockRegistry::getBlocksForPanel();

        expect($panelBlocks)->toBeArray();
        expect($panelBlocks[0])->toHaveKeys(['key', 'label', 'icon', 'blocks']);
    });

    it('generates unique block IDs', function () {
        $id1 = BlockRegistry::generateBlockId();
        $id2 = BlockRegistry::generateBlockId();

        expect($id1)->toStartWith('block_');
        expect($id2)->toStartWith('block_');
        expect($id1)->not->toBe($id2);
    });
});

describe('BlockRegistry validation', function () {
    beforeEach(function () {
        BlockRegistry::registerMany([
            ContainerBlock::class,
            HeadingBlock::class,
            TextBlock::class,
        ]);
    });

    it('validates a valid layout structure', function () {
        $layout = [
            'root' => [
                'id' => 'block_1',
                'type' => 'container',
                'props' => [],
                'children' => [
                    [
                        'id' => 'block_2',
                        'type' => 'heading',
                        'props' => ['content' => 'Hello'],
                        'children' => [],
                    ],
                ],
            ],
        ];

        $errors = BlockRegistry::validateLayout($layout);

        expect($errors)->toBeEmpty();
    });

    it('returns error for layout without root', function () {
        $layout = [];

        $errors = BlockRegistry::validateLayout($layout);

        expect($errors)->toContain('Layout must have a root element');
    });

    it('returns error for block without type', function () {
        $layout = [
            'root' => [
                'id' => 'block_1',
                'children' => [],
            ],
        ];

        $errors = BlockRegistry::validateLayout($layout);

        expect($errors)->not->toBeEmpty();
    });

    it('returns error for unknown block type', function () {
        $layout = [
            'root' => [
                'id' => 'block_1',
                'type' => 'unknown_type',
                'children' => [],
            ],
        ];

        $errors = BlockRegistry::validateLayout($layout);

        expect($errors)->toContain("Unknown block type 'unknown_type' at root");
    });
});
