<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class DocumentFieldGroup extends Forms\Components\Group
{
    protected ?Closure $modifyFieldGroupSelectUsing = null;

    protected array $extraFieldGroupRepeaterItemActions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->key('documentFieldGroup');

        if (blank($this->childComponents)) {

            $this->schema(function () {

                $components[] = $this->getFieldGroupRepeaterComponent();

                return $components;

            });
        }
    }

    public function modifyFieldGroupSelectUsing(?Closure $closure): static
    {
        $this->modifyFieldGroupSelectUsing = $closure;

        return $this;
    }

    public function extraFieldGroupRepeaterItemActions(array $actions): static
    {
        $this->extraFieldGroupRepeaterItemActions = $actions;

        return $this;
    }

    public function getFieldGroupablesSortColumn(): string
    {
        return 'order';
    }

    public function getFieldGroupSortColumn()
    {
        return 'sort';
    }

    public function getFieldGroupRepeaterComponent()
    {
        return FieldGroupRepeater::make('fieldGroupables')
            ->live()
            ->columnSpanFull()
            ->addActionLabel(fn () => __('inspirecms::inspirecms.add_xxx', ['name' => Str::lower(__('inspirecms::inspirecms.field_group'))]))
            ->label(Str::plural(__('inspirecms::inspirecms.field_group')))
            ->validationAttribute(Str::lower(Str::plural(__('inspirecms::inspirecms.field_group'))))
            ->collapsible()
            ->reorderable()->orderColumn($this->getFieldGroupablesSortColumn())
            ->reorderableWithButtons()
            ->addAction(fn (Forms\Components\Actions\Action $action) => $action->extraAttributes(['class' => 'w-full'], true))
            ->fieldGroupRecordOrderAttribute($this->getFieldGroupSortColumn())
            ->modifyRecordSelectUsing($this->modifyFieldGroupSelectUsing)   // custom select field to add field group
            ->modifyRecordSelectOptionQueryUsing(function ($query, FieldGroupRepeater $component) {

                $existingFieldGroupIds = array_values(
                    array_filter(
                        array_map(
                            fn ($item) => is_array($item) ? data_get($item, 'field_group_id') : null,
                            $component->getState() ?? [],
                        )
                    )
                );

                if (count($existingFieldGroupIds) > 0) {
                    $query->whereKeyNot($existingFieldGroupIds);
                }

                return $query
                    ->with(['fields']) // load preview
                    ->where('active', true);
            })
            ->itemStateFromAttachFieldGroupUsing(function (array $data, FieldGroupRepeater $component) {
                $id = $data['recordId'] ?? null;
                if ($id === null) {
                    return [];
                }

                $fieldGroup = $component->getFieldGroupRelationshipQuery()->find($id);

                $itemState = $this->getFieldGroupsItemStateFromFieldGroup($fieldGroup);

                return $itemState;
            })
            ->itemLabel(fn (array $state): ?string => data_get($state, 'field_group_title'))
            ->mutateRelationshipDataBeforeFillUsing(function (array $data, Model | DocumentType $record) {

                $records = $record->fieldGroups
                    ->sortBy($this->getFieldGroupablesSortColumn())
                    ->mapWithKeys(fn ($fieldGroup) => [$fieldGroup->getKey() => $fieldGroup]);

                $formattedData = $this->getFieldGroupsItemStateFromFieldGroup($data['field_group_id'] ?? null, $records);

                return $formattedData;
            })
            ->schema($this->getFieldGroupsRepeaterSchema())
            ->extraItemActions($this->extraFieldGroupRepeaterItemActions);
    }

    /**
     * @param  ?Collection  $existingFieldGroups  The field group collection that key by primary key.
     */
    protected function getFieldGroupsItemStateFromFieldGroup(null | Model | int | string $fieldGroup, $existingFieldGroups = null): array
    {
        if ($fieldGroup === null) {
            return [];
        }

        if ($fieldGroup instanceof Model) {
            // skip
        } elseif (! $fieldGroup instanceof Model && $existingFieldGroups != null) {
            $fieldGroup = $existingFieldGroups->find($fieldGroup);
        } else {
            return [];
        }

        $fieldGroupSortColumn = $this->getFieldGroupSortColumn();

        return [
            'field_group_id' => $fieldGroup->getKey(),
            'field_group_title' => $fieldGroup->title,
            'field_group_fields' => $fieldGroup->fields
                ?->sortBy($fieldGroupSortColumn)
                ->map(function ($field) {
                    $data = $field->only([
                        'label',
                        'type',
                    ]);
                    $data['icon'] = FilamentFieldGroup::getFieldTypeIcon($field->type);
                    return $data;
                })
                ->toArray(),
        ];
    }

    protected function getFieldGroupsRepeaterSchema(): array
    {
        return [
            Forms\Components\Hidden::make('field_group_id'),
            Forms\Components\Hidden::make('field_group_title'),
            Forms\Components\Hidden::make('field_group_fields'),
            $this->getPreviewFieldsComponent(),
        ];
    }

    protected static function getPreviewFieldsComponent(): Forms\Components\Component
    {
        return Forms\Components\ViewField::make('preview_fields')
            ->label(__('inspirecms::inspirecms.preview_fields'))
            ->view('inspirecms::filament.forms.components.field-group-preview')
            ->afterStateHydrated(fn ($component, $get) => $component->state($get('field_group_fields'))) ;
    }
}
