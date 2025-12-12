<?php

declare(strict_types=1);

namespace SolutionForest\InspireCmsVisualEditor\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlockTemplate extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'block_data' => 'array',
        'tags' => 'array',
        'is_global' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function getTable(): string
    {
        return config('visual-editor.table_prefix', 'cms_') . 'block_templates';
    }

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            config('inspirecms.auth.model', \SolutionForest\InspireCms\Models\User::class),
            'created_by'
        );
    }

    /**
     * Scope to public templates.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to global blocks.
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by tags.
     */
    public function scopeWithTags($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Create an instance of this block template.
     */
    public function createInstance(): array
    {
        $blockData = $this->block_data;

        // If this is a global block, reference it
        if ($this->is_global) {
            return [
                'id' => 'block_' . uniqid(),
                'type' => 'global_block',
                'props' => [
                    'templateId' => $this->id,
                ],
                'children' => [],
            ];
        }

        // Otherwise, return a copy with new IDs
        return $this->regenerateIds($blockData);
    }

    /**
     * Regenerate IDs for a block structure.
     */
    protected function regenerateIds(array $block): array
    {
        $block['id'] = 'block_' . uniqid();

        if (isset($block['children'])) {
            $block['children'] = array_map(
                fn ($child) => $this->regenerateIds($child),
                $block['children']
            );
        }

        return $block;
    }

    /**
     * Create a block template from an existing block.
     */
    public static function createFromBlock(array $block, string $name, ?string $category = null): self
    {
        return static::create([
            'name' => $name,
            'type' => $block['type'] ?? 'unknown',
            'category' => $category,
            'block_data' => $block,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Get available template categories.
     */
    public static function getCategories(): array
    {
        return [
            'custom' => __('visual-editor::visual-editor.templates.categories.custom'),
            'layout' => __('visual-editor::visual-editor.templates.categories.layout'),
            'content' => __('visual-editor::visual-editor.templates.categories.content'),
            'cta' => __('visual-editor::visual-editor.templates.categories.cta'),
            'hero' => __('visual-editor::visual-editor.templates.categories.hero'),
            'feature' => __('visual-editor::visual-editor.templates.categories.feature'),
            'testimonial' => __('visual-editor::visual-editor.templates.categories.testimonial'),
            'pricing' => __('visual-editor::visual-editor.templates.categories.pricing'),
            'team' => __('visual-editor::visual-editor.templates.categories.team'),
            'footer' => __('visual-editor::visual-editor.templates.categories.footer'),
        ];
    }

    /**
     * Scope to templates accessible by a user.
     */
    public function scopeAccessibleBy($query, ?int $userId = null)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_public', true);
            if ($userId) {
                $q->orWhere('created_by', $userId);
            }
        });
    }

    /**
     * Scope search by name or description.
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
