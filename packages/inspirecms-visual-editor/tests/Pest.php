<?php

use SolutionForest\InspireCmsVisualEditor\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Unit', 'Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeValidBlock', function () {
    return $this
        ->toBeArray()
        ->toHaveKeys(['id', 'type']);
});

expect()->extend('toBeValidLayout', function () {
    return $this
        ->toBeArray()
        ->toHaveKey('type')
        ->and($this->value['type'])->toBe('container');
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function createBlock(string $type, array $settings = []): array
{
    return [
        'id' => 'block_' . uniqid(),
        'type' => $type,
        'settings' => $settings,
        'styles' => [],
        'children' => [],
    ];
}
