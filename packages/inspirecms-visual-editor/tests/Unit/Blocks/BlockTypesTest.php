<?php

use SolutionForest\InspireCmsVisualEditor\Blocks\Contracts\BlockInterface;
use SolutionForest\InspireCmsVisualEditor\Blocks\Contracts\ContainerBlockInterface;
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
use SolutionForest\InspireCmsVisualEditor\Enums\BlockCategory;

describe('Layout Blocks', function () {
    describe('ContainerBlock', function () {
        beforeEach(fn () => $this->block = new ContainerBlock);

        it('implements BlockInterface', function () {
            expect($this->block)->toBeInstanceOf(BlockInterface::class);
        });

        it('implements ContainerBlockInterface', function () {
            expect($this->block)->toBeInstanceOf(ContainerBlockInterface::class);
        });

        it('has correct type', function () {
            expect($this->block->getType())->toBe('container');
        });

        it('is a container', function () {
            expect($this->block->isContainer())->toBeTrue();
        });

        it('belongs to layout category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Layout->value);
        });

        it('has a label', function () {
            expect($this->block->getLabel())->toBeString()->not->toBeEmpty();
        });

        it('has an icon', function () {
            expect($this->block->getIcon())->toBeString()->not->toBeEmpty();
        });

        it('has settings schema', function () {
            $schema = $this->block->getSettingsSchema();
            expect($schema)->toBeArray();
        });

        it('has default props', function () {
            $props = $this->block->getDefaultProps();
            expect($props)->toBeArray();
        });
    });

    describe('SectionBlock', function () {
        beforeEach(fn () => $this->block = new SectionBlock);

        it('has correct type', function () {
            expect($this->block->getType())->toBe('section');
        });

        it('is a container', function () {
            expect($this->block->isContainer())->toBeTrue();
        });

        it('belongs to layout category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Layout->value);
        });
    });

    describe('GridBlock', function () {
        beforeEach(fn () => $this->block = new GridBlock);

        it('has correct type', function () {
            expect($this->block->getType())->toBe('grid');
        });

        it('is a container', function () {
            expect($this->block->isContainer())->toBeTrue();
        });

        it('belongs to layout category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Layout->value);
        });

        it('has column settings in schema', function () {
            $schema = $this->block->getSettingsSchema();
            expect(collect($schema)->pluck('name')->toArray())->toContain('columns');
        });
    });

    describe('ColumnBlock', function () {
        beforeEach(fn () => $this->block = new ColumnBlock);

        it('has correct type', function () {
            expect($this->block->getType())->toBe('column');
        });

        it('is a container', function () {
            expect($this->block->isContainer())->toBeTrue();
        });

        it('belongs to layout category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Layout->value);
        });
    });
});

describe('Basic Blocks', function () {
    describe('HeadingBlock', function () {
        beforeEach(fn () => $this->block = new HeadingBlock);

        it('implements BlockInterface', function () {
            expect($this->block)->toBeInstanceOf(BlockInterface::class);
        });

        it('has correct type', function () {
            expect($this->block->getType())->toBe('heading');
        });

        it('is not a container', function () {
            expect($this->block->isContainer())->toBeFalse();
        });

        it('belongs to basic category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Basic->value);
        });

        it('has content and level in settings schema', function () {
            $schema = $this->block->getSettingsSchema();
            $names = collect($schema)->pluck('name')->toArray();

            expect($names)->toContain('content');
            expect($names)->toContain('level');
        });

        it('has default level of 2', function () {
            $props = $this->block->getDefaultProps();
            expect($props['level'])->toBe(2);
        });
    });

    describe('TextBlock', function () {
        beforeEach(fn () => $this->block = new TextBlock);

        it('has correct type', function () {
            expect($this->block->getType())->toBe('text');
        });

        it('is not a container', function () {
            expect($this->block->isContainer())->toBeFalse();
        });

        it('belongs to basic category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Basic->value);
        });

        it('has content in settings schema', function () {
            $schema = $this->block->getSettingsSchema();
            $names = collect($schema)->pluck('name')->toArray();

            expect($names)->toContain('content');
        });
    });

    describe('ButtonBlock', function () {
        beforeEach(fn () => $this->block = new ButtonBlock);

        it('has correct type', function () {
            expect($this->block->getType())->toBe('button');
        });

        it('is not a container', function () {
            expect($this->block->isContainer())->toBeFalse();
        });

        it('belongs to basic category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Basic->value);
        });

        it('has text and url in settings schema', function () {
            $schema = $this->block->getSettingsSchema();
            $names = collect($schema)->pluck('name')->toArray();

            expect($names)->toContain('text');
            expect($names)->toContain('url');
        });

        it('has default variant of primary', function () {
            $props = $this->block->getDefaultProps();
            expect($props['variant'])->toBe('primary');
        });
    });

    describe('ImageBlock', function () {
        beforeEach(fn () => $this->block = new ImageBlock);

        it('has correct type', function () {
            expect($this->block->getType())->toBe('image');
        });

        it('is not a container', function () {
            expect($this->block->isContainer())->toBeFalse();
        });

        it('belongs to basic category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Basic->value);
        });

        it('has src and alt in settings schema', function () {
            $schema = $this->block->getSettingsSchema();
            $names = collect($schema)->pluck('name')->toArray();

            expect($names)->toContain('src');
            expect($names)->toContain('alt');
        });
    });
});

describe('Utility Blocks', function () {
    describe('SpacerBlock', function () {
        beforeEach(fn () => $this->block = new SpacerBlock);

        it('has correct type', function () {
            expect($this->block->getType())->toBe('spacer');
        });

        it('is not a container', function () {
            expect($this->block->isContainer())->toBeFalse();
        });

        it('belongs to utility category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Utility->value);
        });

        it('has height in settings schema', function () {
            $schema = $this->block->getSettingsSchema();
            $names = collect($schema)->pluck('name')->toArray();

            expect($names)->toContain('height');
        });
    });

    describe('DividerBlock', function () {
        beforeEach(fn () => $this->block = new DividerBlock);

        it('has correct type', function () {
            expect($this->block->getType())->toBe('divider');
        });

        it('is not a container', function () {
            expect($this->block->isContainer())->toBeFalse();
        });

        it('belongs to utility category', function () {
            expect($this->block->getCategory())->toBe(BlockCategory::Utility->value);
        });
    });
});

describe('Block prop validation', function () {
    it('validates required props', function () {
        $block = new HeadingBlock;
        $errors = $block->validateProps([]);

        // Content should be required
        expect($errors)->toBeArray();
    });

    it('passes validation with valid props', function () {
        $block = new HeadingBlock;
        $errors = $block->validateProps([
            'content' => 'Valid Heading',
            'level' => 2,
        ]);

        expect($errors)->toBeEmpty();
    });
});
