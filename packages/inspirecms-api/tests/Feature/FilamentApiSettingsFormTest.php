<?php

use Filament\Forms\Components\Section;
use SolutionForest\InspireCmsApi\Filament\Forms\Components\ApiSettingsForm;

test('document type schema returns array of components', function () {
    $schema = ApiSettingsForm::getDocumentTypeSchema();

    expect($schema)->toBeArray();
    expect(count($schema))->toBeGreaterThan(0);
});

test('document type schema contains api configuration section', function () {
    $schema = ApiSettingsForm::getDocumentTypeSchema();

    $hasApiSection = collect($schema)->filter(function ($component) {
        return $component instanceof Section
            && str_contains($component->getHeading() ?? '', 'API');
    })->isNotEmpty();

    expect($hasApiSection)->toBeTrue();
});

test('field schema returns array of components', function () {
    $schema = ApiSettingsForm::getFieldSchema();

    expect($schema)->toBeArray();
    expect(count($schema))->toBeGreaterThan(0);
});

test('field schema contains api settings section', function () {
    $schema = ApiSettingsForm::getFieldSchema();

    $hasApiSection = collect($schema)->filter(function ($component) {
        return $component instanceof Section;
    })->isNotEmpty();

    expect($hasApiSection)->toBeTrue();
});

test('mutateFormDataBeforeFill handles json string', function () {
    $data = [
        'api_settings' => '{"enabled":true,"slug":"test"}',
    ];

    $result = ApiSettingsForm::mutateFormDataBeforeFill($data);

    expect($result['api_settings'])->toBeArray();
    expect($result['api_settings']['enabled'])->toBeTrue();
    expect($result['api_settings']['slug'])->toBe('test');
});

test('mutateFormDataBeforeFill handles array input', function () {
    $data = [
        'api_settings' => [
            'enabled' => true,
            'slug' => 'test',
        ],
    ];

    $result = ApiSettingsForm::mutateFormDataBeforeFill($data);

    expect($result['api_settings'])->toBeArray();
    expect($result['api_settings']['enabled'])->toBeTrue();
});

test('mutateFormDataBeforeFill converts default_includes array to string', function () {
    $data = [
        'api_settings' => [
            'default_includes' => ['author', 'children'],
        ],
    ];

    $result = ApiSettingsForm::mutateFormDataBeforeFill($data);

    expect($result['api_settings']['default_includes'])->toBe('author,children');
});

test('mutateFormDataBeforeSave converts default_includes to array', function () {
    $data = [
        'name' => 'Test Type',
        'api_settings' => [
            'enabled' => true,
            'default_includes' => 'author, children, parent',
        ],
    ];

    $result = ApiSettingsForm::mutateFormDataBeforeSave($data);

    expect($result['api_settings']['default_includes'])->toBeArray();
    expect($result['api_settings']['default_includes'])->toContain('author');
    expect($result['api_settings']['default_includes'])->toContain('children');
    expect($result['api_settings']['default_includes'])->toContain('parent');
});

test('mutateFormDataBeforeSave generates slug from name when enabled', function () {
    $data = [
        'name' => 'Blog Posts',
        'api_settings' => [
            'enabled' => true,
            'slug' => '',
        ],
    ];

    $result = ApiSettingsForm::mutateFormDataBeforeSave($data);

    expect($result['api_settings']['slug'])->toBe('blog-posts');
});

test('mutateFormDataBeforeSave preserves custom slug', function () {
    $data = [
        'name' => 'Blog Posts',
        'api_settings' => [
            'enabled' => true,
            'slug' => 'custom-posts',
        ],
    ];

    $result = ApiSettingsForm::mutateFormDataBeforeSave($data);

    expect($result['api_settings']['slug'])->toBe('custom-posts');
});

test('mutateFormDataBeforeSave handles empty default_includes', function () {
    $data = [
        'name' => 'Test',
        'api_settings' => [
            'enabled' => true,
            'default_includes' => '',
        ],
    ];

    $result = ApiSettingsForm::mutateFormDataBeforeSave($data);

    expect($result['api_settings']['default_includes'])->toBeArray();
    expect($result['api_settings']['default_includes'])->toBeEmpty();
});

test('mutateFormDataBeforeFill handles null api_settings', function () {
    $data = [
        'api_settings' => null,
    ];

    $result = ApiSettingsForm::mutateFormDataBeforeFill($data);

    expect($result['api_settings'])->toBeNull();
});

test('mutateFormDataBeforeFill handles invalid json gracefully', function () {
    $data = [
        'api_settings' => 'invalid json {{{',
    ];

    $result = ApiSettingsForm::mutateFormDataBeforeFill($data);

    // Should return empty array or handle gracefully
    expect($result['api_settings'])->toBeArray();
});
