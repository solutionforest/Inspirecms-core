<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIGenerationHistory extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
        'result' => 'array',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function getTable(): string
    {
        return config('visual-editor.table_prefix', 'cms_') . 'ai_generation_history';
    }

    /**
     * Get the user who made this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('inspirecms.auth.model', \SolutionForest\InspireCms\Models\User::class),
            'user_id'
        );
    }

    /**
     * Get the related layout.
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(VisualLayout::class, 'layout_id');
    }

    /**
     * Scope to successful requests.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by provider.
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Get total tokens used.
     */
    public function getTotalTokensAttribute(): int
    {
        return ($this->input_tokens ?? 0) + ($this->output_tokens ?? 0);
    }

    /**
     * Get estimated cost based on provider pricing.
     */
    public function getEstimatedCostAttribute(): float
    {
        $inputCostPerToken = match ($this->provider) {
            'openai' => match ($this->model) {
                'gpt-4' => 0.00003,
                'gpt-4-turbo' => 0.00001,
                'gpt-3.5-turbo' => 0.0000015,
                default => 0.00001,
            },
            'anthropic' => match ($this->model) {
                'claude-3-opus' => 0.000015,
                'claude-3-sonnet' => 0.000003,
                'claude-3-haiku' => 0.00000025,
                default => 0.000003,
            },
            default => 0,
        };

        $outputCostPerToken = match ($this->provider) {
            'openai' => match ($this->model) {
                'gpt-4' => 0.00006,
                'gpt-4-turbo' => 0.00003,
                'gpt-3.5-turbo' => 0.000002,
                default => 0.00003,
            },
            'anthropic' => match ($this->model) {
                'claude-3-opus' => 0.000075,
                'claude-3-sonnet' => 0.000015,
                'claude-3-haiku' => 0.00000125,
                default => 0.000015,
            },
            default => 0,
        };

        return (($this->input_tokens ?? 0) * $inputCostPerToken) +
               (($this->output_tokens ?? 0) * $outputCostPerToken);
    }

    /**
     * Log a successful generation.
     */
    public static function logSuccess(
        string $type,
        string $prompt,
        array $result,
        string $provider,
        string $model,
        ?int $inputTokens = null,
        ?int $outputTokens = null,
        ?int $durationMs = null,
        ?string $layoutId = null,
        ?array $context = null
    ): self {
        return static::create([
            'type' => $type,
            'prompt' => $prompt,
            'context' => $context,
            'result' => $result,
            'provider' => $provider,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'duration_ms' => $durationMs,
            'status' => 'success',
            'layout_id' => $layoutId,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Log a failed generation.
     */
    public static function logError(
        string $type,
        string $prompt,
        string $errorMessage,
        string $provider,
        string $model,
        ?array $context = null
    ): self {
        return static::create([
            'type' => $type,
            'prompt' => $prompt,
            'context' => $context,
            'result' => [],
            'provider' => $provider,
            'model' => $model,
            'status' => 'error',
            'error_message' => $errorMessage,
            'user_id' => auth()->id(),
        ]);
    }
}
