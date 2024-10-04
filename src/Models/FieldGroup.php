<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\FilamentFieldGroup\Models\FieldGroup as BaseModel;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class FieldGroup extends BaseModel
{
    /**
     * Get all of the docuemnt types that are assigned this field group.
     */
    public function documentTypes(): MorphToMany
    {
        return $this->morphedByMany(InspireCmsConfig::getDocumentTypeModelClass(), 'groupabled', InspireCmsConfig::getFieldGroupableTableName());
    }

    public function groupabled(): HasMany
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

            $fiFormConfig = FilamentFieldGroup::getFieldTypeConfig($field->type, $field->config);

            if (! $fiFormConfig) {
                continue;
            }

            $fiFormComponentFQCN = Arr::first(Arr::pluck($fiFormConfig->getFormComponents(), 'component'));
            if (! $fiFormComponentFQCN) {
                throw new \Exception("The field type config class {$fiFormConfig} does not have a FormComponent attribute.");
            }

            $fiFormComponent = $fiFormComponentFQCN::make($field->name);

            // @todo - some components may not have these methods
            $fiFormComponent->label($field->label);
            $fiFormComponent->helperText($field->instructions);
            $fiFormComponent->required($field->mandatory);
            $fiFormComponent->statePath(
                implode('.', [$this->name, $field->name])
            );

            $fiFormConfig->applyConfig($fiFormComponent);

            $schema[] = $fiFormComponent;
        }

        return \Filament\Forms\Components\Section::make($this->title)
            ->schema($schema);
    }

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
