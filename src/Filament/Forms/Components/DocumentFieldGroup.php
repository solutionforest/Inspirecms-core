<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Models\CmsDocumentType;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class DocumentFieldGroup extends Forms\Components\Group
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->key('documentFieldGroup');

        if (blank($this->childComponents)) {

            $this->schema(function () {

                $components[] = $this->getDisplayParentFieldGroupComponent();
                $components[] = $this->getFieldGroupRepeaterComponent();

                return $components;

            });
        }
    }

    public function getMorphFieldGroupsSortColumn(): string
    {
        return 'order';
    }

    public function getFieldGroupSortColumn()
    {
        return 'sort';
    }

    public function getDisplayParentFieldGroupComponent()
    {
        // Display parent field group used
        return Forms\Components\Group::make()
            ->key('parentFieldGroupsPreview')
            ->label(__('inspirecms::inspirecms.parent_xxx', ['name' => strtolower(Str::plural(__('inspirecms::inspirecms.field_group')))]))
            ->schema(function ($get) {

                $parentId = $get('parent_id');
                if (! $parentId) {
                    return [];
                }

                // Query to find parent documentType
                $parent = InspireCmsConfig::getDocumentTypeModelClass()::query()
                    ->with('fieldGroups')
                    ->find($parentId);

                if (! $parent) {
                    return [];
                }
                $parentAncestors = collect($parent->ancestors())->push($parent);

                $fieldGroupState = $parentAncestors->pluck('fieldGroups')
                    ->flatMap(fn ($fieldGroups) => collect($fieldGroups)->map(fn ($fieldGroup) => $this->getFieldGroupsItemStateFromFieldGroup($fieldGroup)))
                    ->mapWithKeys(fn ($stateItem) => [(string) Str::uuid() => $stateItem])
                    ->toArray();

                $components = collect($fieldGroupState)
                    ->map(
                        fn ($itemState, $key) => Forms\Components\Section::make($itemState['field_group_title'])
                            ->description(__('inspirecms::inspirecms.hints.inherited_from_parent_document_type'))
                            ->schema([
                                $this->getPreviewFieldsComponent($itemState['field_group_fields'] ?? [])->name($key),
                            ])
                    )
                    ->all();

                return $components;
            })
            ->extraAttributes(['class' => 'preview-fields-with-bg']);
    }

    public function getFieldGroupRepeaterComponent()
    {
        return FieldGroupRepeater::make('morphFieldGroups')
            ->columnSpanFull()
            ->addActionLabel(fn () => __('inspirecms::inspirecms.add_xxx', ['name' => Str::lower(__('inspirecms::inspirecms.field_group'))]))
            ->label(Str::plural(__('inspirecms::inspirecms.field_group')))
            ->validationAttribute(Str::lower(Str::plural(__('inspirecms::inspirecms.field_group'))))
            ->collapsible()
            ->reorderable()->orderColumn($this->getMorphFieldGroupsSortColumn())
            ->reorderableWithButtons()
            ->addAction(fn (Forms\Components\Actions\Action $action) => $action->extraAttributes(['class' => 'w-full'], true))
            ->fieldGroupRecordOrderAttribute($this->getFieldGroupSortColumn())
            // ->modifyRecordSelectUsing(fn ($select) => $select)   // custom select field to add field group
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
            ->mutateRelationshipDataBeforeFillUsing(function (array $data, Model | CmsDocumentType $record) {

                $records = $record->fieldGroups
                    ->sortBy($this->getMorphFieldGroupsSortColumn())
                    ->mapWithKeys(fn ($fieldGroup) => [$fieldGroup->getKey() => $fieldGroup]);

                $formattedData = $this->getFieldGroupsItemStateFromFieldGroup($data['field_group_id'] ?? null, $records);

                return $formattedData;
            })
            ->schema($this->getFieldGroupsRepeaterSchema());
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
                ->map(fn ($field) => $field->only([
                    'label',
                    'type',
                ]))
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

    protected static function getPreviewFieldsComponent($fieldGroupFields = null): Forms\Components\Component
    {
        return Forms\Components\Placeholder::make('preview_fields')
            ->label(__('inspirecms::inspirecms.preview_fields'))
            ->content(function ($get) use ($fieldGroupFields) {

                $fieldsData = $fieldGroupFields ?? $get('field_group_fields');

                if (! $fieldsData) {
                    return null;
                }

                $previewHtmlString = collect($fieldsData)
                    ->map(fn ($arr) => <<<Html
                        <div class="dark:ring-white/20 gap-1.5 grid grid-cols-3 lg:grid-cols-4 items-center mb-4 ring-1 ring-gray-900/10 rounded-md shadow-sm">
                            <span class="p-4 bg-gray-200 dark:!bg-gray-700 rounded-l-md">
                                {$arr['type']}
                            </span>
                            <span class="p-4 col-span-2 lg:col-span-3 truncate">
                                {$arr['label']}
                            </span>
                        </div>
                    Html)
                    ->implode('');

                return new HtmlString($previewHtmlString);
            });
    }
}
