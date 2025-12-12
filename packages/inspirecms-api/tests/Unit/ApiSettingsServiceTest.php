<?php

use SolutionForest\InspireCmsApi\Services\ApiSettingsService;
use Illuminate\Database\Eloquent\Model;
use Mockery;

beforeEach(function () {
    $this->service = new ApiSettingsService();
});

test('returns defaults when no api_settings', function () {
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->api_settings = null;
    $mockDocumentType->name = 'Blog Posts';
    $mockDocumentType->slug = 'blog-posts';

    $settings = $this->service->getSettings($mockDocumentType);

    expect($settings['enabled'])->toBeFalse();
    expect($settings['public_read'])->toBeFalse();
    expect($settings['public_write'])->toBeFalse();
});

test('merges stored settings with defaults', function () {
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->api_settings = [
        'enabled' => true,
        'public_read' => true,
    ];
    $mockDocumentType->name = 'Blog Posts';
    $mockDocumentType->slug = 'blog-posts';

    $settings = $this->service->getSettings($mockDocumentType);

    expect($settings['enabled'])->toBeTrue();
    expect($settings['public_read'])->toBeTrue();
    expect($settings['public_write'])->toBeFalse(); // Default value
});

test('parses json string api_settings', function () {
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->api_settings = json_encode(['enabled' => true]);
    $mockDocumentType->name = 'Products';
    $mockDocumentType->slug = 'products';

    $settings = $this->service->getSettings($mockDocumentType);

    expect($settings['enabled'])->toBeTrue();
});

test('isEnabled returns correct value', function () {
    $enabledType = Mockery::mock(Model::class);
    $enabledType->api_settings = ['enabled' => true];
    $enabledType->name = 'Enabled';
    $enabledType->slug = 'enabled';

    $disabledType = Mockery::mock(Model::class);
    $disabledType->api_settings = ['enabled' => false];
    $disabledType->name = 'Disabled';
    $disabledType->slug = 'disabled';

    expect($this->service->isEnabled($enabledType))->toBeTrue();
    expect($this->service->isEnabled($disabledType))->toBeFalse();
});

test('getSlug returns custom slug when set', function () {
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->api_settings = [
        'enabled' => true,
        'slug' => 'custom-slug',
    ];
    $mockDocumentType->name = 'Blog Posts';
    $mockDocumentType->slug = 'blog-posts';

    $slug = $this->service->getSlug($mockDocumentType);

    expect($slug)->toBe('custom-slug');
});

test('getSlug falls back to name slug', function () {
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->api_settings = ['enabled' => true];
    $mockDocumentType->name = 'Blog Posts';
    $mockDocumentType->slug = 'blog-posts';

    $slug = $this->service->getSlug($mockDocumentType);

    expect($slug)->toBe('blog-posts');
});

test('isOperationAllowed checks allowed_operations', function () {
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->api_settings = [
        'allowed_operations' => ['index', 'show'],
    ];
    $mockDocumentType->name = 'Products';
    $mockDocumentType->slug = 'products';

    expect($this->service->isOperationAllowed($mockDocumentType, 'index'))->toBeTrue();
    expect($this->service->isOperationAllowed($mockDocumentType, 'show'))->toBeTrue();
    expect($this->service->isOperationAllowed($mockDocumentType, 'store'))->toBeFalse();
    expect($this->service->isOperationAllowed($mockDocumentType, 'destroy'))->toBeFalse();
});

test('isPublicReadEnabled returns correct value', function () {
    $publicType = Mockery::mock(Model::class);
    $publicType->api_settings = ['public_read' => true];
    $publicType->name = 'Public';
    $publicType->slug = 'public';

    $privateType = Mockery::mock(Model::class);
    $privateType->api_settings = ['public_read' => false];
    $privateType->name = 'Private';
    $privateType->slug = 'private';

    expect($this->service->isPublicReadEnabled($publicType))->toBeTrue();
    expect($this->service->isPublicReadEnabled($privateType))->toBeFalse();
});

test('isPublicWriteEnabled returns correct value', function () {
    $writeableType = Mockery::mock(Model::class);
    $writeableType->api_settings = ['public_write' => true];
    $writeableType->name = 'Writeable';
    $writeableType->slug = 'writeable';

    $readOnlyType = Mockery::mock(Model::class);
    $readOnlyType->api_settings = ['public_write' => false];
    $readOnlyType->name = 'ReadOnly';
    $readOnlyType->slug = 'readonly';

    expect($this->service->isPublicWriteEnabled($writeableType))->toBeTrue();
    expect($this->service->isPublicWriteEnabled($readOnlyType))->toBeFalse();
});

test('getFieldSettings returns defaults for unconfigured field', function () {
    $mockField = Mockery::mock(Model::class);
    $mockField->api_settings = null;

    $settings = $this->service->getFieldSettings($mockField);

    expect($settings['exposed'])->toBeTrue();
    expect($settings['readable'])->toBeTrue();
    expect($settings['writable'])->toBeTrue();
    expect($settings['alias'])->toBeNull();
});

test('getFieldSettings merges stored settings', function () {
    $mockField = Mockery::mock(Model::class);
    $mockField->api_settings = [
        'exposed' => true,
        'writable' => false,
        'alias' => 'custom_name',
    ];

    $settings = $this->service->getFieldSettings($mockField);

    expect($settings['exposed'])->toBeTrue();
    expect($settings['readable'])->toBeTrue(); // Default
    expect($settings['writable'])->toBeFalse();
    expect($settings['alias'])->toBe('custom_name');
});

test('isFieldExposed returns correct value', function () {
    $exposedField = Mockery::mock(Model::class);
    $exposedField->api_settings = ['exposed' => true];

    $hiddenField = Mockery::mock(Model::class);
    $hiddenField->api_settings = ['exposed' => false];

    expect($this->service->isFieldExposed($exposedField))->toBeTrue();
    expect($this->service->isFieldExposed($hiddenField))->toBeFalse();
});

test('getFieldAlias returns alias when set', function () {
    $fieldWithAlias = Mockery::mock(Model::class);
    $fieldWithAlias->api_settings = ['alias' => 'my_alias'];

    $fieldWithoutAlias = Mockery::mock(Model::class);
    $fieldWithoutAlias->api_settings = [];

    expect($this->service->getFieldAlias($fieldWithAlias))->toBe('my_alias');
    expect($this->service->getFieldAlias($fieldWithoutAlias))->toBeNull();
});

afterEach(function () {
    Mockery::close();
});
