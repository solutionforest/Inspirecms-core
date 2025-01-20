@php
    use Filament\Support\Enums\Alignment;
    use Filament\Support\Enums\VerticalAlignment;
    use Filament\Support\Facades\FilamentView;
    use Filament\Tables\Columns\Column;
    use Filament\Tables\Columns\ColumnGroup;
    use Filament\Tables\Enums\ActionsPosition;
    use Filament\Tables\Enums\FiltersLayout;
    use Filament\Tables\Enums\RecordCheckboxPosition;
    use Illuminate\Support\Str;

    $columns = $getVisibleColumns();
    $contentGrid = $getContentGrid();
    $columnsLayout = $getTableColumnsLayout();
    $collapsibleColumnsLayout = $getCollapsibleTableColumnsLayout();
    $records = $getPaginationOptions();
    $statePath = $getStatePath();

    $maxItems = $getMaxItems();

@endphp

<div>
    <div 
        x-data="{ 
            selectedRecords: $wire.$entangle('{{ $statePath }}'),
            maxItems: @js($maxItems),
            isRecordSelected(key) {
                return this.selectedRecords?.includes(key) ?? false;
            },
            selectedRecordsCount() {
                return this.selectedRecords?.length ?? 0;
            },
            selectedRecordsCountText() {
                let message = @js(trans('inspirecms::inspirecms.total_xxx_selected'));
                return message?.replace(':count', this.selectedRecordsCount());
            },
            init() {
                this.$watch('selectedRecords', (value) => {
                    if (this.maxItems && value?.length > this.maxItems) {
                        this.selectedRecords = value.slice(value.length - this.maxItems);
                    }
                });
            }
        }"
    >
        <x-filament-tables::container>
            <x-filament-tables::table>

                <div class="mx-3 py-1.5">
                    <p x-text="selectedRecordsCountText" class="text-sm"></p>
                </div>

                <x-slot:header>
                    @if (! $hasTableColumnsLayout())
                        <x-filament-tables::row>

                            {{-- Checkbox cell --}}
                            <x-filament-tables::header-cell></x-filament-tables::header-cell>

                            @foreach ($columns as $column)
                                @php
                                    $columnWidth = $column->getWidth();
                                @endphp

                                <x-filament-tables::header-cell
                                    tag="th"
                                    :actively-sorted="$getSortColumn() === $column->getName()"
                                    :alignment="$column->getAlignment()"
                                    :name="$column->getName()"
                                    :sortable="$column->isSortable()"
                                    :sort-direction="$getSortDirection()"
                                    :wrap="$column->isHeaderWrapped()"
                                    @class([
                                        'fi-table-header-cell-' . str($column->getName())->camel()->kebab(),
                                                'w-full' => blank($columnWidth) && $column->canGrow(default: false),
                                                '[&:not(:first-of-type)]:border-s [&:not(:last-of-type)]:border-e border-gray-200 dark:border-white/5' => $column->getGroup(),
                                    ])
                                    @style([
                                        ('width: ' . $columnWidth) => filled($columnWidth),
                                    ])
                                >
                                    {{ $column->getLabel() }}
                                </x-filament-tables::header-cell>
                            @endforeach
                        </x-filament-tables::row>
                    @endif
                </x-slot>

                @if (! $hasTableColumnsLayout())
                    @foreach ($records as $index => $record)
                        @php
                            $recordKey = $getRecordKey($record);

                            $collapsibleColumnsLayout?->record($record);
                            $hasCollapsibleColumnsLayout = (bool) $collapsibleColumnsLayout?->isVisible();

                        @endphp
                        <x-filament-tables::row
                            :alpine-selected="'isRecordSelected(\'' . $recordKey . '\')'"
                            :wire:key="$this->getId() . '.table.records.' . $recordKey"
                        >
                            <x-filament-tables::selection.cell>
                                <x-filament-tables::selection.checkbox
                                    :label="__('filament-tables::table.fields.bulk_select_record.label', ['key' => $recordKey])"
                                    :value="$recordKey"
                                    x-model="selectedRecords"
                                    x-ref="rowCheckbox_{{ $index }}"
                                    class="fi-ta-record-checkbox mx-3 my-4"
                                />
                            </x-filament-tables::selection.cell>

                            @foreach ($columns as $column)
                                @php
                                    $column->record($record);
                                    $column->rowLoop($loop->parent);
                                @endphp
                                
                                <x-filament-tables::cell
                                    :wire:key="$this->getId() . '.table.record.' . $recordKey . '.column.' . $column->getName()"
                                    :attributes="
                                        \Filament\Support\prepare_inherited_attributes($column->getExtraCellAttributeBag())
                                            ->class([
                                                'fi-table-cell-' . str($column->getName())->camel()->kebab(),
                                                match ($column->getVerticalAlignment()) {
                                                    VerticalAlignment::Start => 'align-top',
                                                    VerticalAlignment::Center => 'align-middle',
                                                    VerticalAlignment::End => 'align-bottom',
                                                    default => null,
                                                },
                                            ])
                                    "
                                    @click="$refs.rowCheckbox_{{ $index }}.click()"
                                >
                                    <x-filament-tables::columns.column
                                        :column="$column"
                                        :is-click-disabled="$column->isClickDisabled()"
                                        :record="$record"
                                    />
                                </x-filament-tables::cell>
                            @endforeach
                        </x-filament-tables::row>
                    @endforeach
                @else
                    
                    <x-filament::grid
                        :default="$contentGrid['default'] ?? 1"
                        :sm="$contentGrid['sm'] ?? null"
                        :md="$contentGrid['md'] ?? null"
                        :lg="$contentGrid['lg'] ?? null"
                        :xl="$contentGrid['xl'] ?? null"
                        :two-xl="$contentGrid['2xl'] ?? null"
                        @class([
                            'fi-ta-content-grid gap-4 p-4 sm:px-6' => $contentGrid,
                            'gap-y-px bg-gray-200 dark:bg-white/5' => ! $contentGrid,
                        ])
                    >

                        @foreach ($records as $record)
                            @php
                                $recordKey = $getRecordKey($record);

                                $collapsibleColumnsLayout?->record($record);
                                $hasCollapsibleColumnsLayout = (bool) $collapsibleColumnsLayout?->isVisible();

                            @endphp
                                <div
                                    @if ($hasCollapsibleColumnsLayout)
                                        x-data="{ isCollapsed: @js($collapsibleColumnsLayout->isCollapsed()) }"
                                        x-init="$dispatch('collapsible-table-row-initialized')"
                                        x-on:collapse-all-table-rows.window="isCollapsed = true"
                                        x-on:expand-all-table-rows.window="isCollapsed = false"
                                        x-bind:class="isCollapsed && 'fi-collapsed'"
                                    @endif
                                    @class([
                                        'fi-ta-record relative h-full bg-white transition duration-75 dark:bg-gray-900',
                                        'rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10' => $contentGrid,
                                        ...$getRecordClasses($record),
                                    ])
                                >
                                    @php
                                        $hasItemBeforeRecordContent = true;
                                        $isRecordCollapsible = $hasCollapsibleColumnsLayout;
                                        $hasItemAfterRecordContent = $isRecordCollapsible;

                                        $recordContentHorizontalPaddingClasses = \Illuminate\Support\Arr::toCssClasses([
                                            'ps-3' => (! $contentGrid) && $hasItemBeforeRecordContent,
                                            'ps-4 sm:ps-6' => (! $contentGrid) && (! $hasItemBeforeRecordContent),
                                            'pe-3' => (! $contentGrid) && $hasItemAfterRecordContent,
                                            'pe-4 sm:pe-6' => (! $contentGrid) && (! $hasItemAfterRecordContent),
                                            'ps-2' => $contentGrid && $hasItemBeforeRecordContent,
                                            'ps-4' => $contentGrid && (! $hasItemBeforeRecordContent),
                                            'pe-2' => $contentGrid && $hasItemAfterRecordContent,
                                            'pe-4' => $contentGrid && (! $hasItemAfterRecordContent),
                                        ]);

                                        $recordActionsClasses = \Illuminate\Support\Arr::toCssClasses([
                                            'md:ps-3' => (! $contentGrid),
                                            'ps-3' => (! $contentGrid) && $hasItemBeforeRecordContent,
                                            'ps-4 sm:ps-6' => (! $contentGrid) && (! $hasItemBeforeRecordContent),
                                            'pe-3' => (! $contentGrid) && $hasItemAfterRecordContent,
                                            'pe-4 sm:pe-6' => (! $contentGrid) && (! $hasItemAfterRecordContent),
                                            'ps-2' => $contentGrid && $hasItemBeforeRecordContent,
                                            'ps-4' => $contentGrid && (! $hasItemBeforeRecordContent),
                                            'pe-2' => $contentGrid && $hasItemAfterRecordContent,
                                            'pe-4' => $contentGrid && (! $hasItemAfterRecordContent),
                                        ]);
                                    @endphp

                                    <div
                                        @class([
                                            'flex items-center',
                                            'ps-1 sm:ps-3' => (! $contentGrid) && $hasItemBeforeRecordContent,
                                            'pe-1 sm:pe-3' => (! $contentGrid) && $hasItemAfterRecordContent,
                                            'ps-1' => $contentGrid && $hasItemBeforeRecordContent,
                                            'pe-1' => $contentGrid && $hasItemAfterRecordContent,
                                        ])
                                    >
                                        <x-filament-tables::selection.checkbox
                                            :label="__('filament-tables::table.fields.bulk_select_record.label', ['key' => $recordKey])"
                                            :value="$recordKey"
                                            x-model="selectedRecords"
                                            class="fi-ta-record-checkbox mx-3 my-4"
                                        />

                                        @php
                                            $recordContentClasses = \Illuminate\Support\Arr::toCssClasses([
                                                $recordContentHorizontalPaddingClasses,
                                                'block w-full',
                                            ]);
                                        @endphp

                                        <div
                                            @class([
                                                'flex w-full flex-col gap-y-3 py-4',
                                                'md:flex-row md:items-center' => ! $contentGrid,
                                            ])
                                        >
                                            <div class="flex-1">
                                                <div
                                                    class="{{ $recordContentClasses }}"
                                                >
                                                    <x-filament-tables::columns.layout
                                                        :components="$columnsLayout"
                                                        :record="$record"
                                                        :record-key="$recordKey"
                                                        :row-loop="$loop"
                                                    />
                                                </div>

                                                @if ($hasCollapsibleColumnsLayout)
                                                    <div
                                                        x-collapse
                                                        x-show="! isCollapsed"
                                                        class="{{ $recordContentHorizontalPaddingClasses }} mt-3"
                                                    >
                                                        {{ $collapsibleColumnsLayout->viewData(['recordKey' => $recordKey]) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                    </div>

                                </div>

                        @endforeach
                    </x-filament::grid>
                @endif

            </x-filament-tables::table>

            @if ((($records instanceof \Illuminate\Contracts\Pagination\Paginator) || ($records instanceof \Illuminate\Contracts\Pagination\CursorPaginator)) &&
                ((! ($records instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)) || $records->total()))
                <x-inspirecms::forms.components.pagination
                    :paginator="$records"
                    :state-path="$statePath"
                    extreme-links
                    class="px-3 py-3 sm:px-6"
                />
            @endif
        </x-filament-tables::container>
    </div>
</div>