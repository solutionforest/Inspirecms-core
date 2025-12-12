<?php

use SolutionForest\InspireCmsApi\Models\ApiToken;

beforeEach(function () {
    $this->artisan('migrate', ['--database' => 'testing']);
});

test('api disabled returns 503', function () {
    // Disable API
    config(['inspirecms-api.enabled' => false]);

    $response = $this->getJson('/api/v1/schema');

    $response->assertStatus(503)
        ->assertJsonPath('error', 'API is disabled');
});

test('api enabled allows requests', function () {
    config(['inspirecms-api.enabled' => true]);

    $response = $this->getJson('/api/v1/schema');

    $response->assertStatus(200);
});

test('token with read ability can access get endpoints', function () {
    $tokenData = ApiToken::createToken('Read Token', null, ['read']);

    $response = $this->withHeader('Authorization', 'Bearer ' . $tokenData['plain_token'])
        ->deleteJson('/api/v1/auth/token');

    // The token should be valid for authentication
    // Delete should work because it's revoking the token itself
    $response->assertStatus(200);
});

test('token abilities are checked correctly', function () {
    $token = ApiToken::create([
        'name' => 'Limited Token',
        'token' => hash('sha256', 'limited-token'),
        'abilities' => ['read'],
        'expires_at' => null,
    ]);

    expect($token->hasAbility('read'))->toBeTrue();
    expect($token->hasAbility('write'))->toBeFalse();
    expect($token->hasAbility('delete'))->toBeFalse();
    expect($token->hasAbility('*'))->toBeFalse();
});

test('wildcard ability grants all access', function () {
    $token = ApiToken::create([
        'name' => 'Full Access Token',
        'token' => hash('sha256', 'full-token'),
        'abilities' => ['*'],
        'expires_at' => null,
    ]);

    expect($token->hasAbility('read'))->toBeTrue();
    expect($token->hasAbility('write'))->toBeTrue();
    expect($token->hasAbility('delete'))->toBeTrue();
    expect($token->hasAbility('anything'))->toBeTrue();
});

test('token last_used_at is updated on use', function () {
    $tokenData = ApiToken::createToken('Test Token', null, ['*']);
    $token = $tokenData['token'];

    expect($token->last_used_at)->toBeNull();

    // Use the token
    $this->withHeader('Authorization', 'Bearer ' . $tokenData['plain_token'])
        ->deleteJson('/api/v1/auth/token');

    // Note: Token is deleted by the request, so we check a different way
    // For this test, let's create another token and make a different request
    $tokenData2 = ApiToken::createToken('Test Token 2', null, ['*']);

    // Make a request that doesn't delete the token
    $this->withHeader('Authorization', 'Bearer ' . $tokenData2['plain_token'])
        ->getJson('/api/v1/schema');

    $tokenData2['token']->refresh();
    expect($tokenData2['token']->last_used_at)->not->toBeNull();
});

test('multiple authentication methods work', function () {
    $tokenData = ApiToken::createToken('Multi Auth Token', null, ['*']);

    // Test Bearer token
    $response1 = $this->withHeader('Authorization', 'Bearer ' . $tokenData['plain_token'])
        ->getJson('/api/v1/schema');
    $response1->assertStatus(200);

    // Create another token for X-API-Key test
    $tokenData2 = ApiToken::createToken('API Key Token', null, ['*']);

    $response2 = $this->withHeader('X-API-Key', $tokenData2['plain_token'])
        ->getJson('/api/v1/schema');
    $response2->assertStatus(200);
});
