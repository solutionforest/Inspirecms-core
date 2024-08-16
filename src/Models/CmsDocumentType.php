<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CmsDocumentType extends Model
{
    use Concerns\NestableTrait;

    protected $guarded = ['id'];

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

    public function pages(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getPageModelClass(), 'document_type_id');
    }

    public function scopeIsRoot($query, bool $condition = true)
    {
        if ($condition) {
            $query->whereNull('parent_id');
        } else {
            $query->whereNotNull('parent_id');
        }
    }
}
