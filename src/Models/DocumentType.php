<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\InspireCms\Base\Enums\DocumentTypeCategory as DocumentTypeCategoryEnum;
use SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory as DocumentTypeCategoryInterface;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\DocumentType as DocumentTypeContract;
use SolutionForest\InspireCms\Observers\DocumentTypeObserver;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Concerns\NestableTrait;

#[ObservedBy(DocumentTypeObserver::class)]
class DocumentType extends BaseModel implements DocumentTypeContract
{
    use Concerns\HasTemplates;
    use NestableTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'show_children_as_table' => 'boolean',
    ];

    public function getFieldsThroughQuery()
    {
        $fieldModel = app(InspireCmsConfig::getFieldModelClass());
        $fieldGroupModel = $this->fieldGroups()->getRelated();

        $q = $fieldModel::query()
            ->withGroupName()
            ->whereExists(
                $this->fieldGroups()->getBaseQuery()
                    ->select($fieldGroupModel->getQualifiedKeyName())
                    ->whereColumn(
                        $fieldGroupModel->getQualifiedKeyName(),
                        $fieldModel->qualifyColumn('group_id'),
                    )
            );

        return $q;
    }

    public function fieldGroups(): MorphToMany
    {
        return $this->morphToMany(InspireCmsConfig::getFieldGroupModelClass(), 'groupabled', InspireCmsConfig::getFieldGroupableTableName())
            ->withPivot(['order', 'inherited_from_id', 'inherited_from_type'])
            ->using(InspireCmsConfig::getFieldGroupableModelClass());
    }

    public function fieldGroupables(): MorphMany
    {
        return $this->morphMany(InspireCmsConfig::getFieldGroupableModelClass(), 'groupabled')
            ->orderBy('order');
    }

    public function inheritedDocumentTypes(): BelongsToMany
    {
        return $this->belongsToMany(InspireCmsConfig::getDocumentTypeModelClass(), InspireCmsConfig::getDocumentTypeInheritanceTableName(), 'document_type_id', 'inherited_document_type_id');
    }

    public function inheritingDocumentTypes(): BelongsToMany
    {
        return $this->belongsToMany(InspireCmsConfig::getDocumentTypeModelClass(), InspireCmsConfig::getDocumentTypeInheritanceTableName(), 'inherited_document_type_id', 'document_type_id');
    }

    public function content(): HasMany
    {
        return $this->hasMany(InspireCmsConfig::getContentModelClass(), 'document_type_id');
    }

    //region Scope(s)
    public function scopeCanBeInherited($query)
    {
        return $query->where('category', static::getCategoryEnumClass()::allCanBeInherited());
    }

    public function scopeIsWebPage($query)
    {
        return $query->where('category', static::getCategoryEnumClass()::Web->value);
    }
    //endregion Scope(s)

    public function isShowChildrenAsTable(): bool
    {
        return $this->show_children_as_table;
    }

    public function isWebPageType(): bool
    {
        return $this->category == DocumentTypeCategoryEnum::Web->value;
    }

    public function canInheriting(): bool
    {
        return $this->getCategoryEnum()?->canInheriting() ?? false;
    }

    public function canBeInherited(): bool
    {
        return $this->getCategoryEnum()?->canBeInherited() ?? false;
    }

    public function getCategoryEnum(): ?DocumentTypeCategoryInterface
    {
        return static::getCategoryEnumClass()::tryFrom($this->category);
    }

    public static function getCategoryEnumClass(): string
    {
        $class = DocumentTypeCategoryEnum::class;

        if (! in_array(DocumentTypeCategoryInterface::class, class_implements($class))) {
            throw new \Exception("The class {$class} must implement the interface \SolutionForest\InspireCms\Base\Enums\Interfaces\DocumentTypeCategory");
        }

        return $class;
    }

    public function inheritDocumentType(string | int | DocumentTypeContract $documentType): bool
    {
        try {
            if (is_string($documentType) || is_int($documentType)) {
                $documentType = static::query()->findOrFail($documentType);
            }

            if (! $documentType->canBeInherited() || ! $this->canInheriting()) {
                return false;
            }

            $this->inheritedDocumentTypes()->syncWithoutDetaching($documentType->getKey());

            $this->inheritFieldGroupsFrom($documentType);

            return true;

        } catch (\Throwable $th) {
            return false;
        }
    }

    public function inheritFieldGroupsFrom(string | int | DocumentTypeContract $documentType): bool
    {
        try {
            if (is_string($documentType) || is_int($documentType)) {
                $documentType = static::query()->findOrFail($documentType);
            }

            if (! $documentType->canBeInherited() || ! $this->canInheriting()) {
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

    public function deteachInheritFieldGroupsFrom(string | int | DocumentTypeContract $documentType): bool
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

    public function canBeParent(): bool
    {
        return $this->exists && $this->isWebPageType();
    }

    public function canHaveParent(): bool
    {
        return $this->exists && ! $this->isRoot() && $this->isWebPageType();
    }

    public function canManageTemplates(): bool
    {
        return $this->exists && $this->isWebPageType();
    }
}
