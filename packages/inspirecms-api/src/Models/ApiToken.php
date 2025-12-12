<?php

namespace SolutionForest\InspireCmsApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\InspireCmsConfig;

class ApiToken extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['token'];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('inspirecms-api.tables.api_tokens', 'cms_api_tokens');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getUserModelClass(), 'user_id');
    }

    /**
     * Generate a new API token.
     */
    public static function generateToken(): string
    {
        return hash(
            config('inspirecms-api.auth.token_hash_algo', 'sha256'),
            Str::random(40)
        );
    }

    /**
     * Create a new token for a user.
     */
    public static function createToken(
        string $name,
        ?string $userId = null,
        array $abilities = ['*'],
        ?int $expiryDays = null
    ): array {
        $plainToken = Str::random(40);
        $hashedToken = hash(config('inspirecms-api.auth.token_hash_algo', 'sha256'), $plainToken);

        $expiryDays = $expiryDays ?? config('inspirecms-api.auth.token_expiry_days');

        $token = static::create([
            'name' => $name,
            'token' => $hashedToken,
            'user_id' => $userId,
            'abilities' => $abilities,
            'expires_at' => $expiryDays ? now()->addDays($expiryDays) : null,
        ]);

        return [
            'token' => $token,
            'plain_token' => $plainToken,
        ];
    }

    /**
     * Find a token by its plain text value.
     */
    public static function findByPlainToken(string $plainToken): ?static
    {
        $hashedToken = hash(config('inspirecms-api.auth.token_hash_algo', 'sha256'), $plainToken);

        return static::where('token', $hashedToken)->first();
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        if (is_null($this->expires_at)) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if the token is valid (not expired).
     */
    public function isValid(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Check if the token has a specific ability.
     */
    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        if (in_array('*', $abilities)) {
            return true;
        }

        return in_array($ability, $abilities);
    }

    /**
     * Update the last used timestamp.
     */
    public function touchLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Revoke the token.
     */
    public function revoke(): bool
    {
        return $this->delete();
    }

    /**
     * Scope to get only valid (non-expired) tokens.
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get expired tokens.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }
}
