<?php

declare(strict_types=1);

namespace SolutionForest\InspireCms\VisualEditor\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\InspireCmsConfig;

class VisualLayoutVersion extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'layout_data' => 'array',
        'settings' => 'array',
    ];

    public function getTable(): string
    {
        return InspireCmsConfig::get('models.table_name_prefix', 'cms_') . 'visual_layout_versions';
    }

    /**
     * Get the parent layout.
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(VisualLayout::class, 'layout_id');
    }

    /**
     * Get the user who created this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            config('inspirecms.auth.model', \SolutionForest\InspireCms\Models\User::class),
            'created_by'
        );
    }

    /**
     * Get a diff between this version and another.
     */
    public function diffWith(VisualLayoutVersion $other): array
    {
        $thisData = $this->layout_data ?? [];
        $otherData = $other->layout_data ?? [];

        return $this->computeDiff($thisData, $otherData);
    }

    /**
     * Compute differences between two arrays.
     */
    protected function computeDiff(array $a, array $b, string $path = ''): array
    {
        $diff = [];

        // Check for additions and modifications
        foreach ($b as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : $key;

            if (! array_key_exists($key, $a)) {
                $diff[] = [
                    'type' => 'added',
                    'path' => $currentPath,
                    'value' => $value,
                ];
            } elseif (is_array($value) && is_array($a[$key])) {
                $diff = array_merge($diff, $this->computeDiff($a[$key], $value, $currentPath));
            } elseif ($a[$key] !== $value) {
                $diff[] = [
                    'type' => 'modified',
                    'path' => $currentPath,
                    'oldValue' => $a[$key],
                    'newValue' => $value,
                ];
            }
        }

        // Check for removals
        foreach ($a as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : $key;

            if (! array_key_exists($key, $b)) {
                $diff[] = [
                    'type' => 'removed',
                    'path' => $currentPath,
                    'value' => $value,
                ];
            }
        }

        return $diff;
    }
}
