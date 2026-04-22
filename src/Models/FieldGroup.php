<?php

namespace SolutionForest\InspireCms\Models;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use SolutionForest\FilamentFieldGroup\Models\FieldGroup as BaseModel;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup as FieldGroupContract;

class FieldGroup extends BaseModel implements FieldGroupContract
{
    /** {@inheritDoc} */
    public function documentTypes()
    {
        return $this->morphedByMany(InspireCmsConfig::getDocumentTypeModelClass(), 'groupabled', InspireCmsConfig::getFieldGroupableTableName());
    }

    /** {@inheritDoc} */
    public function groupabled()
    {
        return $this->hasMany(InspireCmsConfig::getFieldGroupableModelClass(), 'field_group_id');
    }

    /**
     * @return Component
     */
    public function toFilamentComponent()
    {
        $schema = [];

        foreach ($this->fields as $field) {

            $fiFormComponent = FieldTypeHelper::buildFieldForFieldType(
                fieldTypeName: $field->type,
                fieldTypeConfig: $field->config,
                name: $field->name,
                label: $field->label,
                helperText: $field->instructions,
                required: $field->mandatory,
                groupName: $this->name,
            );

            if (! $fiFormComponent) {
                continue;
            }

            $schema[] = $fiFormComponent;
        }

        return Section::make($this->title)
            ->collapsible()
            ->schema($schema);
    }

    // region Scopes
    /** {@inheritDoc} */
    public function scopeWhereActive($query, bool $condition = true)
    {
        return $query->where('active', $condition);
    }
    // endregion Scopes

    public static function booting()
    {
        // Do before parent::boot()
        static::deleting(function (self $group) {
            // Check if the group is associated with any document types
            if ($group->documentTypes()->exists()) {
                throw new \Exception('Cannot delete this field group because it is in use.');
            }
        });
    }
}
