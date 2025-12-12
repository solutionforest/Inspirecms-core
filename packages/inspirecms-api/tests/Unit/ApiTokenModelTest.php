<?php

use SolutionForest\InspireCmsApi\Models\ApiToken;

beforeEach(function () {
    $this->artisan('migrate', ['--database' => 'testing']);
});

test('can create token', function () {
    $result = ApiToken::createToken('Test Token', null, ['read', 'write']);

    expect($result)->toHaveKeys(['token', 'plain_token']);
    expect($result['token'])->toBeInstanceOf(ApiToken::class);
    expect($result['plain_token'])->toBeString();
    expect(strlen($result['plain_token']))->toBe(40);
});

test('token is hashed correctly', function () {
    $result = ApiToken::createToken('Test Token');

    $expectedHash = hash('sha256', $result['plain_token']);
    expect($result['token']->token)->toBe($expectedHash);
});

test('can find token by plain text', function () {
    $result = ApiToken::createToken('Test Token');

    $found = ApiToken::findByPlainToken($result['plain_token']);

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($result['token']->id);
});

test('returns null for invalid plain token', function () {
    $found = ApiToken::findByPlainToken('invalid-token-that-does-not-exist');

    expect($found)->toBeNull();
});

test('token expiration is set correctly', function () {
    $result = ApiToken::createToken('Expiring Token', null, ['*'], 30);

    expect($result['token']->expires_at)->not->toBeNull();
    expect($result['token']->expires_at->isAfter(now()))->toBeTrue();
    expect($result['token']->expires_at->isBefore(now()->addDays(31)))->toBeTrue();
});

test('token without expiry never expires', function () {
    config(['inspirecms-api.auth.token_expiry_days' => null]);

    $result = ApiToken::createToken('Never Expiring Token');

    expect($result['token']->expires_at)->toBeNull();
    expect($result['token']->isExpired())->toBeFalse();
});

test('expired token is detected', function () {
    $token = ApiToken::create([
        'name' => 'Expired Token',
        'token' => hash('sha256', 'expired'),
        'abilities' => ['*'],
        'expires_at' => now()->subDay(),
    ]);

    expect($token->isExpired())->toBeTrue();
    expect($token->isValid())->toBeFalse();
});

test('valid token is detected', function () {
    $token = ApiToken::create([
        'name' => 'Valid Token',
        'token' => hash('sha256', 'valid'),
        'abilities' => ['*'],
        'expires_at' => now()->addDay(),
    ]);

    expect($token->isExpired())->toBeFalse();
    expect($token->isValid())->toBeTrue();
});

test('abilities are stored as array', function () {
    $result = ApiToken::createToken('Test', null, ['read', 'write']);

    expect($result['token']->abilities)->toBeArray();
    expect($result['token']->abilities)->toContain('read');
    expect($result['token']->abilities)->toContain('write');
});

test('has ability check works', function () {
    $token = ApiToken::create([
        'name' => 'Limited Token',
        'token' => hash('sha256', 'limited'),
        'abilities' => ['read', 'write'],
    ]);

    expect($token->hasAbility('read'))->toBeTrue();
    expect($token->hasAbility('write'))->toBeTrue();
    expect($token->hasAbility('delete'))->toBeFalse();
});

test('wildcard ability matches everything', function () {
    $token = ApiToken::create([
        'name' => 'Super Token',
        'token' => hash('sha256', 'super'),
        'abilities' => ['*'],
    ]);

    expect($token->hasAbility('read'))->toBeTrue();
    expect($token->hasAbility('write'))->toBeTrue();
    expect($token->hasAbility('delete'))->toBeTrue();
    expect($token->hasAbility('admin'))->toBeTrue();
});

test('can revoke token', function () {
    $result = ApiToken::createToken('To Revoke');
    $tokenId = $result['token']->id;

    $result['token']->revoke();

    expect(ApiToken::find($tokenId))->toBeNull();
});

test('touch last used updates timestamp', function () {
    $token = ApiToken::create([
        'name' => 'Touch Test',
        'token' => hash('sha256', 'touch'),
        'abilities' => ['*'],
    ]);

    expect($token->last_used_at)->toBeNull();

    $token->touchLastUsed();

    expect($token->last_used_at)->not->toBeNull();
    expect($token->last_used_at->isToday())->toBeTrue();
});

test('valid scope returns non-expired tokens', function () {
    // Create valid token
    ApiToken::create([
        'name' => 'Valid',
        'token' => hash('sha256', 'valid1'),
        'abilities' => ['*'],
        'expires_at' => now()->addDay(),
    ]);

    // Create expired token
    ApiToken::create([
        'name' => 'Expired',
        'token' => hash('sha256', 'expired1'),
        'abilities' => ['*'],
        'expires_at' => now()->subDay(),
    ]);

    // Create never-expiring token
    ApiToken::create([
        'name' => 'Never Expires',
        'token' => hash('sha256', 'never'),
        'abilities' => ['*'],
        'expires_at' => null,
    ]);

    $validTokens = ApiToken::valid()->get();

    expect($validTokens)->toHaveCount(2);
    expect($validTokens->pluck('name')->toArray())->toContain('Valid');
    expect($validTokens->pluck('name')->toArray())->toContain('Never Expires');
});

test('expired scope returns only expired tokens', function () {
    ApiToken::create([
        'name' => 'Valid',
        'token' => hash('sha256', 'valid2'),
        'abilities' => ['*'],
        'expires_at' => now()->addDay(),
    ]);

    ApiToken::create([
        'name' => 'Expired',
        'token' => hash('sha256', 'expired2'),
        'abilities' => ['*'],
        'expires_at' => now()->subDay(),
    ]);

    $expiredTokens = ApiToken::expired()->get();

    expect($expiredTokens)->toHaveCount(1);
    expect($expiredTokens->first()->name)->toBe('Expired');
});
