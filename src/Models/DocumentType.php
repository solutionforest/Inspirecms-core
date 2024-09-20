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
    use Concerns\BelongToCmsNestableTree;
    use Concerns\HasTemplates;
    use Concerns\NestableTrait;

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

    public static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            if ($model->isDirty('is_element_type')) {
                $model->children()->update(['is_element_type' => $model->is_element_type]);
            }
        });
    }

    protected function getParentId()
    {
        return $this->{$this->getNestableParentIdColumn()} ?? $this->fallbackParentId();
    }

    protected function getNestableParentIdColumn()
    {
        return 'parent_id';
    }
}
