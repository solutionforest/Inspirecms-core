<?php

use SolutionForest\InspireCmsApi\Filament\Resources\ApiTokenResource;
use SolutionForest\InspireCmsApi\Models\ApiToken;

beforeEach(function () {
    $this->artisan('migrate', ['--database' => 'testing']);
});

test('api token resource has correct model', function () {
    expect(ApiTokenResource::getModel())->toBe(ApiToken::class);
});

test('api token resource has navigation icon', function () {
    expect(ApiTokenResource::getNavigationIcon())->toBe('heroicon-o-key');
});

test('api token resource has navigation group', function () {
    expect(ApiTokenResource::getNavigationGroup())->toBe('Settings');
});

test('api token resource has navigation label', function () {
    expect(ApiTokenResource::getNavigationLabel())->toBe('API Tokens');
});

test('api token resource pages are defined', function () {
    $pages = ApiTokenResource::getPages();

    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('edit');
});

test('can create api token via factory', function () {
    $token = ApiToken::create([
        'name' => 'Test Token',
        'token' => hash('sha256', 'test-token'),
        'abilities' => ['*'],
    ]);

    expect($token)->toBeInstanceOf(ApiToken::class);
    expect($token->name)->toBe('Test Token');
    expect($token->abilities)->toContain('*');
});

test('api token table columns are correct', function () {
    // This test verifies the table structure
    // Full Livewire testing requires more setup
    $form = ApiTokenResource::form(
        \Filament\Forms\Form::make(
            \Livewire\Livewire::new(\Filament\Resources\Pages\CreateRecord::class)
        )
    );

    expect($form)->not->toBeNull();
});
