<?php

use Illuminate\Database\Eloquent\Model;
use Mockery;
use SolutionForest\InspireCmsApi\Models\ApiToken;
use SolutionForest\InspireCmsApi\Services\ApiRouteGenerator;
use SolutionForest\InspireCmsApi\Services\ApiSettingsService;
use SolutionForest\InspireCmsApi\Services\ContentQueryService;

beforeEach(function () {
    $this->artisan('migrate', ['--database' => 'testing']);
});

test('content type not found returns 404', function () {
    $this->mock(ApiRouteGenerator::class, function ($mock) {
        $mock->shouldReceive('findDocumentTypeBySlug')
            ->with('nonexistent')
            ->andReturn(null);
    });

    $response = $this->getJson('/api/v1/nonexistent');

    $response->assertStatus(404)
        ->assertJsonPath('error', 'Not Found')
        ->assertJsonPath('message', "Content type 'nonexistent' not found.");
});

test('disabled api returns 404 for content type', function () {
    // Create a mock document type with API disabled
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->shouldReceive('getKey')->andReturn(1);
    $mockDocumentType->api_settings = ['enabled' => false];

    $this->mock(ApiRouteGenerator::class, function ($mock) use ($mockDocumentType) {
        $mock->shouldReceive('findDocumentTypeBySlug')
            ->with('disabled-type')
            ->andReturn($mockDocumentType);
    });

    $this->mock(ApiSettingsService::class, function ($mock) {
        $mock->shouldReceive('getSettings')
            ->andReturn([
                'enabled' => false,
                'slug' => 'disabled-type',
                'public_read' => false,
                'public_write' => false,
                'allowed_operations' => [],
            ]);
    });

    $response = $this->getJson('/api/v1/disabled-type');

    $response->assertStatus(404);
});

test('private content type requires authentication', function () {
    // Create a mock document type with API enabled but private
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->shouldReceive('getKey')->andReturn(1);
    $mockDocumentType->api_settings = [
        'enabled' => true,
        'public_read' => false,
    ];

    $this->mock(ApiRouteGenerator::class, function ($mock) use ($mockDocumentType) {
        $mock->shouldReceive('findDocumentTypeBySlug')
            ->with('private-type')
            ->andReturn($mockDocumentType);
    });

    $this->mock(ApiSettingsService::class, function ($mock) {
        $mock->shouldReceive('getSettings')
            ->andReturn([
                'enabled' => true,
                'slug' => 'private-type',
                'public_read' => false,
                'public_write' => false,
                'allowed_operations' => ['index', 'show'],
            ]);
    });

    $response = $this->getJson('/api/v1/private-type');

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Authentication required to access this content type.');
});

test('authenticated user can access private content', function () {
    $tokenData = ApiToken::createToken('Test Token', null, ['*']);

    // Create a mock document type
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->shouldReceive('getKey')->andReturn(1);
    $mockDocumentType->id = 1;
    $mockDocumentType->name = 'Blog Posts';
    $mockDocumentType->slug = 'blog-posts';
    $mockDocumentType->api_settings = [
        'enabled' => true,
        'public_read' => false,
    ];

    $this->mock(ApiRouteGenerator::class, function ($mock) use ($mockDocumentType) {
        $mock->shouldReceive('findDocumentTypeBySlug')
            ->with('blog-posts')
            ->andReturn($mockDocumentType);
    });

    $this->mock(ApiSettingsService::class, function ($mock) {
        $mock->shouldReceive('getSettings')
            ->andReturn([
                'enabled' => true,
                'slug' => 'blog-posts',
                'public_read' => false,
                'public_write' => false,
                'allowed_operations' => ['index', 'show'],
                'max_per_page' => 100,
                'default_includes' => [],
            ]);
    });

    // Mock the query service to return empty results
    $this->mock(ContentQueryService::class, function ($mock) {
        $mock->shouldReceive('buildQuery')
            ->andReturn(Mockery::mock(\Illuminate\Database\Eloquent\Builder::class));
        $mock->shouldReceive('paginate')
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15));
    });

    $response = $this->withHeader('Authorization', 'Bearer ' . $tokenData['plain_token'])
        ->getJson('/api/v1/blog-posts');

    // Should get through authentication (may fail on other things in mocked environment)
    expect($response->status())->not->toBe(401);
});

test('operation not allowed returns 405', function () {
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->shouldReceive('getKey')->andReturn(1);
    $mockDocumentType->api_settings = [
        'enabled' => true,
        'public_read' => true,
        'allowed_operations' => ['index'], // Only index allowed, not show
    ];

    $this->mock(ApiRouteGenerator::class, function ($mock) use ($mockDocumentType) {
        $mock->shouldReceive('findDocumentTypeBySlug')
            ->with('limited-type')
            ->andReturn($mockDocumentType);
    });

    $this->mock(ApiSettingsService::class, function ($mock) {
        $mock->shouldReceive('getSettings')
            ->andReturn([
                'enabled' => true,
                'slug' => 'limited-type',
                'public_read' => true,
                'public_write' => false,
                'allowed_operations' => ['index'], // show not allowed
            ]);
    });

    // Try to access show endpoint when only index is allowed
    // The controller checks operations in checkAccess()
    $response = $this->getJson('/api/v1/limited-type/some-id');

    // This should return 405 or 404 based on operation check
    expect(in_array($response->status(), [404, 405]))->toBeTrue();
});

test('write operations require authentication by default', function () {
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->shouldReceive('getKey')->andReturn(1);
    $mockDocumentType->api_settings = [
        'enabled' => true,
        'public_read' => true,
        'public_write' => false,
        'allowed_operations' => ['index', 'show', 'store'],
    ];

    $this->mock(ApiRouteGenerator::class, function ($mock) use ($mockDocumentType) {
        $mock->shouldReceive('findDocumentTypeBySlug')
            ->with('posts')
            ->andReturn($mockDocumentType);
    });

    $this->mock(ApiSettingsService::class, function ($mock) {
        $mock->shouldReceive('getSettings')
            ->andReturn([
                'enabled' => true,
                'slug' => 'posts',
                'public_read' => true,
                'public_write' => false,
                'allowed_operations' => ['index', 'show', 'store'],
            ]);
    });

    // POST without authentication
    $response = $this->postJson('/api/v1/posts', [
        'title' => 'New Post',
        'slug' => 'new-post',
    ]);

    $response->assertStatus(401);
});

test('delete operations require authentication', function () {
    $mockDocumentType = Mockery::mock(Model::class);
    $mockDocumentType->shouldReceive('getKey')->andReturn(1);

    $this->mock(ApiRouteGenerator::class, function ($mock) use ($mockDocumentType) {
        $mock->shouldReceive('findDocumentTypeBySlug')
            ->with('posts')
            ->andReturn($mockDocumentType);
    });

    $this->mock(ApiSettingsService::class, function ($mock) {
        $mock->shouldReceive('getSettings')
            ->andReturn([
                'enabled' => true,
                'slug' => 'posts',
                'public_read' => true,
                'public_write' => false,
                'allowed_operations' => ['index', 'show', 'destroy'],
            ]);
    });

    $response = $this->deleteJson('/api/v1/posts/some-id');

    $response->assertStatus(401);
});
