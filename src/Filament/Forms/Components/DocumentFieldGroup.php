<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Models\CmsDocumentType;

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
            ->mutateRelationshipDataBeforeFillUsing(function (array $data, Model | CmsDocumentType $record) {

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
