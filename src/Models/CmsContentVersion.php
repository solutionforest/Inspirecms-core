<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsContentVersion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'version_date' => 'datetime',
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    const CREATED_AT = 'version_date';

    const UPDATED_AT = 'version_date';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getContentVersionTableName());
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getContentModelClass(), 'content_id');
    }

    public function propertyData(): BelongsTo
    {
        return $this->belongsTo(InspireCmsConfig::getPropertyDataModelClass(), 'property_data_id');
    }
}
