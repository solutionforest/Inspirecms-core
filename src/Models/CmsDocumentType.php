<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsDocumentType extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'can_use_at_root' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(InspireCmsConfig::getDocumentTypeTableName());
    }

    public function fieldGroups(): MorphToMany
    {
        return $this->morphToMany(InspireCmsConfig::getFieldGroupModelClass(), 'model', InspireCmsConfig::getComponentFieldGroupTableName())
            ->orderBy('sort');
    }

    public function morphFieldGroups(): MorphMany
    {
        return $this->morphMany(InspireCmsConfig::getComponentFieldGroupModelClass(), 'model')
            ->orderBy('order');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getContentModelClass(), 'document_type_id');
    }
}
