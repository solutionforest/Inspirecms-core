<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Base\BaseModel;
use SolutionForest\InspireCms\Models\Contracts\DocumentType as DocumentTypeContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class DocumentType extends BaseModel implements DocumentTypeContract
{
    use Concerns\HasTemplates;

    protected $guarded = ['id'];

    protected $casts = [
        'can_use_at_root' => 'boolean',
        'is_element_type' => 'boolean',
    ];

    public function fieldGroups(): MorphToMany
    {
        return $this->morphToMany(InspireCmsConfig::getFieldGroupModelClass(), 'groupabled', InspireCmsConfig::getFieldGroupableTableName())
            ->orderBy('sort');
    }

    public function fieldGroupables(): MorphMany
    {
        return $this->morphMany(InspireCmsConfig::getFieldGroupableModelClass(), 'groupabled')
            ->orderBy('order');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getContentModelClass(), 'document_type_id');
    }
}
