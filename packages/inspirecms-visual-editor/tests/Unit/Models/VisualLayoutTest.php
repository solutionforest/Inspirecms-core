<?php

use SolutionForest\InspireCmsVisualEditor\Models\VisualLayout;
use SolutionForest\InspireCmsVisualEditor\Models\VisualLayoutVersion;

describe('VisualLayout Model', function () {
    it('can be created', function () {
        $layout = VisualLayout::create([
            'name' => 'Test Layout',
            'layout_data' => [
                'id' => 'root',
                'type' => 'container',
                'children' => [],
            ],
        ]);

        expect($layout)->toBeInstanceOf(VisualLayout::class);
        expect($layout->name)->toBe('Test Layout');
        expect($layout->exists)->toBeTrue();
    });

    it('casts layout_data to array', function () {
        $layoutData = [
            'id' => 'root',
            'type' => 'container',
            'children' => [
                ['id' => 'child1', 'type' => 'heading'],
            ],
        ];

        $layout = VisualLayout::create([
            'name' => 'Test Layout',
            'layout_data' => $layoutData,
        ]);

        expect($layout->layout_data)->toBeArray();
        expect($layout->layout_data['type'])->toBe('container');
        expect($layout->layout_data['children'])->toHaveCount(1);
    });

    it('generates slug from name', function () {
        $layout = VisualLayout::create([
            'name' => 'My Test Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
        ]);

        expect($layout->slug)->toBe('my-test-layout');
    });

    it('can be soft deleted', function () {
        $layout = VisualLayout::create([
            'name' => 'Test Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
        ]);

        $layout->delete();

        expect($layout->trashed())->toBeTrue();
        expect(VisualLayout::withTrashed()->find($layout->id))->not->toBeNull();
    });

    it('can have versions', function () {
        $layout = VisualLayout::create([
            'name' => 'Test Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
        ]);

        $version = $layout->versions()->create([
            'version_number' => 1,
            'layout_data' => ['id' => 'root', 'type' => 'container', 'version' => 1],
        ]);

        expect($layout->versions)->toHaveCount(1);
        expect($version->visual_layout_id)->toBe($layout->id);
    });

    it('filters by status scope', function () {
        VisualLayout::create([
            'name' => 'Published Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
            'status' => 'published',
        ]);

        VisualLayout::create([
            'name' => 'Draft Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
            'status' => 'draft',
        ]);

        expect(VisualLayout::where('status', 'published')->count())->toBe(1);
        expect(VisualLayout::where('status', 'draft')->count())->toBe(1);
    });

    it('can update layout_data', function () {
        $layout = VisualLayout::create([
            'name' => 'Test Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container', 'children' => []],
        ]);

        $newData = [
            'id' => 'root',
            'type' => 'container',
            'children' => [
                ['id' => 'block1', 'type' => 'heading', 'settings' => ['content' => 'Hello']],
            ],
        ];

        $layout->update(['layout_data' => $newData]);
        $layout->refresh();

        expect($layout->layout_data['children'])->toHaveCount(1);
        expect($layout->layout_data['children'][0]['type'])->toBe('heading');
    });

    it('can be searched by name', function () {
        VisualLayout::create([
            'name' => 'Homepage Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
        ]);

        VisualLayout::create([
            'name' => 'About Page Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
        ]);

        $results = VisualLayout::where('name', 'like', '%Homepage%')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->name)->toBe('Homepage Layout');
    });
});

describe('VisualLayoutVersion Model', function () {
    it('belongs to a layout', function () {
        $layout = VisualLayout::create([
            'name' => 'Test Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
        ]);

        $version = VisualLayoutVersion::create([
            'visual_layout_id' => $layout->id,
            'version_number' => 1,
            'layout_data' => ['id' => 'root', 'type' => 'container'],
        ]);

        expect($version->visualLayout)->toBeInstanceOf(VisualLayout::class);
        expect($version->visualLayout->id)->toBe($layout->id);
    });

    it('increments version number', function () {
        $layout = VisualLayout::create([
            'name' => 'Test Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
        ]);

        $version1 = $layout->versions()->create([
            'version_number' => 1,
            'layout_data' => ['version' => 1],
        ]);

        $version2 = $layout->versions()->create([
            'version_number' => 2,
            'layout_data' => ['version' => 2],
        ]);

        expect($version1->version_number)->toBe(1);
        expect($version2->version_number)->toBe(2);
    });

    it('can store change description', function () {
        $layout = VisualLayout::create([
            'name' => 'Test Layout',
            'layout_data' => ['id' => 'root', 'type' => 'container'],
        ]);

        $version = $layout->versions()->create([
            'version_number' => 1,
            'layout_data' => ['id' => 'root', 'type' => 'container'],
            'change_description' => 'Initial version',
        ]);

        expect($version->change_description)->toBe('Initial version');
    });
});
