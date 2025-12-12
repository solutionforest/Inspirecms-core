<?php

use Livewire\Livewire;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ButtonBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ContainerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\HeadingBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\SpacerBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\TextBlock;
use SolutionForest\InspireCmsVisualEditor\Livewire\BlockPanel;

beforeEach(function () {
    BlockRegistry::clear();
    BlockRegistry::registerMany([
        ContainerBlock::class,
        HeadingBlock::class,
        TextBlock::class,
        ButtonBlock::class,
        SpacerBlock::class,
    ]);
});

describe('BlockPanel Livewire Component', function () {
    it('can be rendered', function () {
        Livewire::test(BlockPanel::class)
            ->assertStatus(200);
    });

    it('displays registered blocks grouped by category', function () {
        Livewire::test(BlockPanel::class)
            ->assertSee('Layout')
            ->assertSee('Basic')
            ->assertSee('Utility');
    });

    it('displays block labels', function () {
        Livewire::test(BlockPanel::class)
            ->assertSee('Container')
            ->assertSee('Heading')
            ->assertSee('Text');
    });

    it('can search for blocks', function () {
        Livewire::test(BlockPanel::class)
            ->set('search', 'heading')
            ->assertSee('Heading')
            ->assertDontSee('Button');
    });

    it('shows no results message when search has no matches', function () {
        Livewire::test(BlockPanel::class)
            ->set('search', 'nonexistentblock')
            ->assertSee('No blocks found');
    });

    it('clears search results', function () {
        Livewire::test(BlockPanel::class)
            ->set('search', 'heading')
            ->set('search', '')
            ->assertSee('Container')
            ->assertSee('Button');
    });

    it('can collapse categories', function () {
        Livewire::test(BlockPanel::class)
            ->call('toggleCategory', 'layout')
            ->assertSet('collapsedCategories.layout', true);
    });

    it('can expand collapsed categories', function () {
        Livewire::test(BlockPanel::class)
            ->set('collapsedCategories', ['layout' => true])
            ->call('toggleCategory', 'layout')
            ->assertSet('collapsedCategories.layout', false);
    });
});
