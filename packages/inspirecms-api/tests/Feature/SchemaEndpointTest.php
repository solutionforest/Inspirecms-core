<?php

use SolutionForest\InspireCmsApi\Services\ApiRouteGenerator;

beforeEach(function () {
    // Mock the ApiRouteGenerator to return test data
    $this->mock(ApiRouteGenerator::class, function ($mock) {
        $mock->shouldReceive('getApiEnabledDocumentTypes')
            ->andReturn(collect([]));
    });
});

test('schema endpoint returns api information', function () {
    $response = $this->getJson('/api/v1/schema');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'api' => ['version', 'prefix'],
            'types',
            'authentication' => ['methods'],
            'rate_limiting',
        ]);
});

test('schema endpoint returns correct api version', function () {
    $response = $this->getJson('/api/v1/schema');

    $response->assertStatus(200)
        ->assertJsonPath('api.version', 'v1')
        ->assertJsonPath('api.prefix', 'api');
});

test('schema endpoint returns authentication methods', function () {
    $response = $this->getJson('/api/v1/schema');

    $response->assertStatus(200)
        ->assertJsonPath('authentication.methods.bearer_token.header', 'Authorization')
        ->assertJsonPath('authentication.methods.api_key.header', 'X-API-Key');
});

test('schema endpoint for unknown type returns 404', function () {
    $this->mock(ApiRouteGenerator::class, function ($mock) {
        $mock->shouldReceive('findDocumentTypeBySlug')
            ->with('unknown-type')
            ->andReturn(null);
    });

    $response = $this->getJson('/api/v1/schema/unknown-type');

    $response->assertStatus(404)
        ->assertJsonPath('error', 'Not Found');
});
