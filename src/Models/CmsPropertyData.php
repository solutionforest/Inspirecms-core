<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsPropertyData extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'property_value' => 'json',
    ];

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getPropertyDataTableName());
    }

    public function contentVersion(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getContentVersionModelClass(), 'property_data_id');
    }

    public function isPublished(): bool
    {
        $publishedAt = $this->published_at;

        if (is_null($publishedAt)) {
            return false;
        }

        return $publishedAt->isPast();
    }
}
