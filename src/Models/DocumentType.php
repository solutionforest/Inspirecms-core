<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory as DocumentTypeCategoryEnum;
use SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory as DocumentTypeCategoryInterface;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\DocumentType as DocumentTypeContract;
use SolutionForest\InspireCms\Observers\DocumentTypeObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class DocumentType extends BaseModel implements DocumentTypeContract
{
    use Concerns\HasTemplates;

    protected $guarded = ['id'];

    protected $casts = [
        'show_as_table' => 'boolean',
        'show_at_root' => 'boolean',
    ];

    public function fields()
    {
        // target model
        $fieldModelClass = InspireCmsConfig::getFieldModelClass();
        // check existance of the field model
        $fieldGroupModelClass = InspireCmsConfig::getFieldGroupModelClass();
        // through table
        $fieldGroupableModelClass = InspireCmsConfig::getFieldGroupableModelClass();

        $fieldModel = app($fieldModelClass);
        $fieldGroupModel = app($fieldGroupModelClass);
        $fieldGroupableModel = app($fieldGroupableModelClass);

        return $this->hasManyThrough(
            $fieldModelClass,
            $fieldGroupableModelClass,
            'groupabled_id',
            'group_id',
            'id',
            'field_group_id',
        )
            ->where($fieldGroupableModel->qualifyColumn('groupabled_type'), $this->getMorphClass())
            ->whereExists(function ($query) use ($fieldGroupModel, $fieldModel) {
                $query
                    ->from($fieldGroupModel->getTable())
                    ->whereColumn(
                        $fieldGroupModel->qualifyColumn('id'),
                        $fieldModel->qualifyColumn('group_id')
                    );
            })
            ->orderBy($fieldGroupableModel->qualifyColumn($fieldGroupableModel->determineOrderColumnName()))
            ->orderBy($fieldModel->qualifyColumn($fieldModel->determineOrderColumnName()));
    }

    public function fieldGroups()
    {
        return $this->morphToMany(InspireCmsConfig::getFieldGroupModelClass(), 'groupabled', InspireCmsConfig::getFieldGroupableTableName())
            ->withPivot(['order', 'inherited_from_id', 'inherited_from_type'])
            ->using(InspireCmsConfig::getFieldGroupableModelClass());
    }

    public function fieldGroupables()
    {
        return $this->morphMany(InspireCmsConfig::getFieldGroupableModelClass(), 'groupabled')
            ->orderBy('order');
    }

    public function inheritedDocumentTypes()
    {
        return $this->belongsToMany(InspireCmsConfig::getDocumentTypeModelClass(), InspireCmsConfig::getDocumentTypeInheritanceTableName(), 'document_type_id', 'inherited_document_type_id');
    }

    public function inheritingDocumentTypes()
    {
        return $this->belongsToMany(InspireCmsConfig::getDocumentTypeModelClass(), InspireCmsConfig::getDocumentTypeInheritanceTableName(), 'inherited_document_type_id', 'document_type_id');
    }

    public function allowedDocumentTypes()
    {
        return $this->belongsToMany(InspireCmsConfig::getDocumentTypeModelClass(), InspireCmsConfig::getAllowedDocumentTypeTableName(), 'id', 'allowed_id');
    }

    public function allowingDocumentTypes()
    {
        return $this->belongsToMany(InspireCmsConfig::getDocumentTypeModelClass(), InspireCmsConfig::getAllowedDocumentTypeTableName(), 'allowed_id', 'id');
    }

    public function content()
    {
        return $this->hasMany(InspireCmsConfig::getContentModelClass(), 'document_type_id');
    }

    // region Scope(s)
    public function scopeCanBeInherited($query, bool $condition = true)
    {
        if ($condition) {
            return $query->where('category', static::getCategoryEnumClass()::allCanBeInherited());
        }

        return $query->whereNot('category', static::getCategoryEnumClass()::allCanBeInherited());
    }

    public function scopeWhereIsWebPage($query, bool $condition = true)
    {
        $webCat = static::getCategoryEnumClass()::Web->value;

        if ($condition) {
            return $query->where('category', $webCat);
        }

        return $query->whereNot('category', $webCat);
    }

    public function scopeWhereCanBeContent($query, bool $condition = true)
    {
        $canBeContentCats = [
            static::getCategoryEnumClass()::Web->value,
            static::getCategoryEnumClass()::Data->value,
        ];
        
        if ($condition) {
            return $query->whereIn('category', $canBeContentCats);
        }

        return $query->whereNotIn('category', $canBeContentCats);
    }
    // endregion Scope(s)

    public function isWebPageType()
    {
        return $this->category == DocumentTypeCategoryEnum::Web->value;
    }

    public function isDataType()
    {
        return $this->category == DocumentTypeCategoryEnum::Data->value;
    }

    public function inheritDocumentType($documentType)
    {
        try {
            if (is_string($documentType) || is_int($documentType)) {
                $documentType = static::query()->findOrFail($documentType);
            }

            if (! ($documentType->display_category?->canBeInherited() ?? false) || ! ($this->display_category?->canInheriting() ?? false)) {
                return false;
            }

            $this->inheritedDocumentTypes()->syncWithoutDetaching($documentType->getKey());

            $this->inheritFieldGroupsFrom($documentType);

            return true;

        } catch (\Throwable $th) {
            return false;
        }
    }

    public function inheritFieldGroupsFrom($documentType)
    {
        try {
            if (is_string($documentType) || is_int($documentType)) {
                $documentType = static::query()->findOrFail($documentType);
            }

            if (! ($documentType->display_category?->canBeInherited() ?? false) || ! ($this->display_category?->canInheriting() ?? false)) {
                return false;
            }

            $ids = $documentType->fieldGroups()
                ->get()
                ->map(fn ($fieldGroup) => $fieldGroup->getKey())
                ->toArray();

            $pivotData = [
                'inherited_from_id' => $documentType->getKey(),
                'inherited_from_type' => $documentType->getMorphClass(),
            ];

            // set the inheritable type and id for existing field groups
            $this->fieldGroupables()->whereIn('field_group_id', $ids)->update($pivotData);

            // filter out the field groups that are already inherited
            $ids = array_diff($ids, $this->fieldGroupables()->pluck('field_group_id')->toArray());

            if (empty($ids)) {
                return true;
            }

            $this->fieldGroups()->syncWithPivotValues(
                $ids,
                $pivotData,
                false
            );

            return true;

        } catch (\Throwable $th) {

            return false;
        }
    }

    public function deteachInheritFieldGroupsFrom($documentType)
    {
        try {
            if (is_string($documentType) || is_int($documentType)) {
                $documentType = static::query()->findOrFail($documentType);
            }

            $ids = $documentType->fieldGroups()
                ->get()
                ->map(fn ($fieldGroup) => $fieldGroup->getKey())
                ->toArray();

            $this->fieldGroups()->detach($ids);

            return true;

        } catch (\Throwable $th) {
            return false;
        }
    }

    public function canManageTemplates()
    {
        return $this->exists && ($this->isWebPageType() || $this->isDataType());
    }

    // region Attribute(s)
    protected function displayCategory(): Attribute
    {
        return Attribute::make(
            get: function () {
                $category = $this->category;
                if (filled($category)) {
                    return static::getCategoryEnumClass()::tryFrom($category);
                }

                return null;
            },
        );
    }
    // endregion Attribute(s)

    public static function boot()
    {
        parent::boot();

        static::observe(DocumentTypeObserver::class);
    }

    /**
     * @return class-string<DocumentTypeCategoryEnum>
     */
    public static function getCategoryEnumClass()
    {
        $class = DocumentTypeCategoryEnum::class;

        if (! in_array(DocumentTypeCategoryInterface::class, class_implements($class))) {
            throw new \Exception("The class {$class} must implement the interface \SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory");
        }

        return $class;
    }
}
