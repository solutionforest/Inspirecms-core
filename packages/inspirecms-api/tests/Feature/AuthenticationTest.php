<?php

use SolutionForest\InspireCmsApi\Models\ApiToken;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Run API token migration
    $this->artisan('migrate', ['--database' => 'testing']);
});

test('can create api token with valid credentials', function () {
    // Create a test user
    $userClass = config('inspirecms.models.fqcn.user');

    // Skip if user model doesn't exist in test environment
    if (! class_exists($userClass)) {
        $this->markTestSkipped('User model not available in test environment');
    }

    $user = $userClass::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/token', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'name' => 'Test Token',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => ['token', 'type', 'expires_at'],
        ]);
})->skip('Requires full InspireCMS user model setup');

test('cannot create token with invalid credentials', function () {
    $response = $this->postJson('/api/v1/auth/token', [
        'email' => 'invalid@example.com',
        'password' => 'wrongpassword',
        'name' => 'Test Token',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('error', 'Unauthorized');
})->skip('Requires full InspireCMS user model setup');

test('token validation rejects missing email', function () {
    $response = $this->postJson('/api/v1/auth/token', [
        'password' => 'password123',
        'name' => 'Test Token',
    ]);

    $response->assertStatus(422);
});

test('token validation rejects missing password', function () {
    $response = $this->postJson('/api/v1/auth/token', [
        'email' => 'test@example.com',
        'name' => 'Test Token',
    ]);

    $response->assertStatus(422);
});

test('can revoke token when authenticated', function () {
    // Create a token directly
    $tokenData = ApiToken::createToken('Test Token', null, ['*']);

    $response = $this->withHeader('Authorization', 'Bearer ' . $tokenData['plain_token'])
        ->deleteJson('/api/v1/auth/token');

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Token revoked successfully.');

    // Verify token is deleted
    expect(ApiToken::find($tokenData['token']->id))->toBeNull();
});

test('cannot revoke token without authentication', function () {
    $response = $this->deleteJson('/api/v1/auth/token');

    $response->assertStatus(401);
});

test('bearer token authentication works', function () {
    $tokenData = ApiToken::createToken('Test Token', null, ['*']);

    // Make any authenticated request
    $response = $this->withHeader('Authorization', 'Bearer ' . $tokenData['plain_token'])
        ->deleteJson('/api/v1/auth/token');

    $response->assertStatus(200);
});

test('api key header authentication works', function () {
    $tokenData = ApiToken::createToken('Test Token', null, ['*']);

    $response = $this->withHeader('X-API-Key', $tokenData['plain_token'])
        ->deleteJson('/api/v1/auth/token');

    $response->assertStatus(200);
});

test('expired token is rejected', function () {
    // Create an expired token
    $token = ApiToken::create([
        'name' => 'Expired Token',
        'token' => hash('sha256', 'expired-token'),
        'abilities' => ['*'],
        'expires_at' => now()->subDay(),
    ]);

    $response = $this->withHeader('Authorization', 'Bearer expired-token')
        ->deleteJson('/api/v1/auth/token');

    $response->assertStatus(401)
        ->assertJsonPath('message', 'API token has expired');
});

test('invalid token is rejected', function () {
    $response = $this->withHeader('Authorization', 'Bearer invalid-token-that-does-not-exist')
        ->deleteJson('/api/v1/auth/token');

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Invalid API token');
});
