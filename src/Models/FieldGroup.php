<?php

namespace SolutionForest\InspireCms\Models;

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
     * @return \Filament\Forms\Components\Component
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
            // $fiFormComponent = FieldTypeHelper::performFormFieldFromConfig($field->type, function ($fiFormConfig, $fiFormComponentFQCN) use ($field) {

            //     $fieldName = $field->name;
            //     $groupName = $this->name;

            //     $statePath = method_exists($field, '') ?
            //         $field->getStatePathWithGroup() :
            //         implode('.', [$groupName, $fieldName]);

            //     if (isset($fiFormConfig->translatable) && $fiFormConfig->translatable) {
            //         return FieldTypeHelper::buildTranslatableField(
            //             typeName: $field->type, 
            //             fieldTypeConfig: $field->config, 
            //             name: $fieldName,
            //             label: $field->label,
            //             helperText: $field->instructions,
            //             required: $field->mandatory,
            //             groupName: $groupName,
            //         );
            //     } else if (is_subclass_of($fiFormComponentFQCN, \Filament\Forms\Components\Field::class)) {
            //         $fiFormComponent = $fiFormComponentFQCN::make($fieldName);

            //         $fiFormComponent->label($field->label);
            //         $fiFormComponent->helperText($field->instructions);
            //         $fiFormComponent->required($field->mandatory);
            //         $fiFormComponent->statePath($statePath);

            //     } else {

            //         $fiFormComponent = null;
            //     }

            //     // if (in_array(\SolutionForest\InspireCms\Fields\Configs\Concerns\HasInnerField::class, class_uses($fiFormConfig))) {

            //     //     $fiFormConfig->setFieldVariable([
            //     //         'name' => $fieldName,
            //     //         'label' => $field->label,
            //     //         'helperText' => $field->instructions,
            //     //         'required' => $field->mandatory,
            //     //         'statePath' => $statePath,
            //     //         'group' => $groupName,
            //     //     ]);

            //     //     $fiFormComponent = $fiFormComponentFQCN::make();
            //     // }

            //     return $fiFormComponent;

            // }, $field->config);

            if (! $fiFormComponent) {
                continue;
            }

            $schema[] = $fiFormComponent;
        }

        return \Filament\Forms\Components\Section::make($this->title)
            ->schema($schema);
    }

    //region Scopes
    /** {@inheritDoc} */
    public function scopeWhereActive($query, bool $condition = true)
    {
        return $query->where('active', $condition);
    }
    //endregion Scopes

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
