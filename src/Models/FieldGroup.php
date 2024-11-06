<?php

namespace SolutionForest\InspireCms\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SolutionForest\FilamentFieldGroup\Models\FieldGroup as BaseModel;
use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup as FieldGroupContract;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class FieldGroup extends BaseModel implements FieldGroupContract
{
    /** @inheritDoc */
    public function documentTypes(): MorphToMany
    {
        return $this->morphedByMany(InspireCmsConfig::getDocumentTypeModelClass(), 'groupabled', InspireCmsConfig::getFieldGroupableTableName());
    }

    /** @inheritDoc */
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

            $fiFormComponent = FieldTypeHelper::performFormFieldFromConfig($field->type, function ($fiFormConfig, $fiFormComponentFQCN) use ($field) {

                $fieldName = $field->name;
                $groupName = $this->name;

                $statePath = method_exists($field, '') ?
                    $field->getStatePathWithGroup() :
                    implode('.', [$groupName, $fieldName]);

                if (is_subclass_of($fiFormComponentFQCN, \Filament\Forms\Components\Field::class)) {
                    $fiFormComponent = $fiFormComponentFQCN::make($fieldName);

                    $fiFormComponent->label($field->label);
                    $fiFormComponent->helperText($field->instructions);
                    $fiFormComponent->required($field->mandatory);
                    $fiFormComponent->statePath($statePath);

                } else {

                    if ($fiFormConfig instanceof \SolutionForest\InspireCms\FieldTypes\Configs\Translate) {
                        $fiFormConfig->setFieldVariable([
                            'name' => $fieldName,
                            'label' => $field->label,
                            'helperText' => $field->instructions,
                            'required' => $field->mandatory,
                            'statePath' => $statePath,
                            'group' => $groupName,
                        ]);
                    }

                    $fiFormComponent = $fiFormComponentFQCN::make();
                }

                return $fiFormComponent;

            }, $field->config);

            if (! $fiFormComponent) {
                continue;
            }

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
