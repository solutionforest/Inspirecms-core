<?php

use Livewire\Livewire;
use SolutionForest\InspireCmsVisualEditor\Blocks\Registry\BlockRegistry;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\HeadingBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\TextBlock;
use SolutionForest\InspireCmsVisualEditor\Blocks\Types\ButtonBlock;
use SolutionForest\InspireCmsVisualEditor\Livewire\SettingsPanel;

beforeEach(function () {
    BlockRegistry::clear();
    BlockRegistry::registerMany([
        HeadingBlock::class,
        TextBlock::class,
        ButtonBlock::class,
    ]);
});

describe('SettingsPanel Livewire Component', function () {
    it('can be rendered', function () {
        Livewire::test(SettingsPanel::class)
            ->assertStatus(200);
    });

    it('shows empty state when no block selected', function () {
        Livewire::test(SettingsPanel::class)
            ->assertSee('Select a block');
    });

    it('displays block settings when block is selected', function () {
        $blockData = [
            'id' => 'block_1',
            'type' => 'heading',
            'settings' => ['content' => 'Test', 'level' => 2],
            'styles' => [],
        ];

        Livewire::test(SettingsPanel::class, ['selectedBlock' => $blockData])
            ->assertSee('Heading')
            ->assertDontSee('Select a block');
    });

    it('can switch between content and style tabs', function () {
        $blockData = [
            'id' => 'block_1',
            'type' => 'heading',
            'settings' => ['content' => 'Test', 'level' => 2],
            'styles' => [],
        ];

        Livewire::test(SettingsPanel::class, ['selectedBlock' => $blockData])
            ->assertSet('activeTab', 'content')
            ->call('setActiveTab', 'style')
            ->assertSet('activeTab', 'style');
    });

    it('updates block settings', function () {
        $blockData = [
            'id' => 'block_1',
            'type' => 'heading',
            'settings' => ['content' => 'Original', 'level' => 2],
            'styles' => [],
        ];

        Livewire::test(SettingsPanel::class, ['selectedBlock' => $blockData])
            ->call('updateSetting', 'content', 'Updated')
            ->assertDispatched('updateBlockSettings');
    });

    it('updates block styles', function () {
        $blockData = [
            'id' => 'block_1',
            'type' => 'heading',
            'settings' => ['content' => 'Test', 'level' => 2],
            'styles' => [],
        ];

        Livewire::test(SettingsPanel::class, ['selectedBlock' => $blockData])
            ->call('setActiveTab', 'style')
            ->call('updateStyle', 'color', '#ff0000')
            ->assertDispatched('updateBlockStyles');
    });

    it('resets when block is deselected', function () {
        $blockData = [
            'id' => 'block_1',
            'type' => 'heading',
            'settings' => ['content' => 'Test'],
            'styles' => [],
        ];

        Livewire::test(SettingsPanel::class, ['selectedBlock' => $blockData])
            ->set('selectedBlock', null)
            ->assertSee('Select a block');
    });
});
