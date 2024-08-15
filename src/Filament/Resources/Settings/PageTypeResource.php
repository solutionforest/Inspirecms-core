<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Filament\Forms\Components\FieldGroupRepeater;
use SolutionForest\InspireCms\Filament\Resources\Settings\PageTypeResource\Pages;
use SolutionForest\InspireCms\Models\CmsPageType;

class PageTypeResource extends Resource
{
    public static function form(Form $form): Form
    {
        $morphFieldGroupsSortColumn = 'sort';
        $getItemStateFromFieldGroup = function (?Model $fieldGroup) {
            if ($fieldGroup === null) {
                return [];
            }

            return [
                'field_group_id' => $fieldGroup->getKey(),
                'field_group_title' => $fieldGroup->title,
                'field_group_fields' => $fieldGroup->fields
                    ?->sortBy('sort')
                    ->map(fn ($field) => $field->only([
                        'label',
                        'type',
                    ])),
            ];
        };

        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->inlineLabel()
                    ->columnSpanFull()
                    ->required(),

                FieldGroupRepeater::make('morphFieldGroups')
                    ->columnSpanFull()
                    ->collapsible()
                    ->reorderable()->orderColumn($morphFieldGroupsSortColumn)
                    ->reorderableWithButtons()
                    ->addAction(fn (Forms\Components\Actions\Action $action) => $action->extraAttributes(['class' => 'w-full'], true))
                    ->addActionLabel(fn () => __('inspirecms::inspirecms.add_xxx', ['name' => Str::lower(__('inspirecms::inspirecms.field_group'))]))
                    ->label(Str::plural(__('inspirecms::inspirecms.field_group')))
                    ->validationAttribute(Str::lower(Str::plural(__('inspirecms::inspirecms.field_group'))))
                    ->modifyRecordSelectUsing(fn ($select) => $select)
                    ->modifyRecordSelectOptionQueryUsing(function ($query, FieldGroupRepeater $component) {

                        $existingFieldGroupIds = array_values(
                            array_filter(
                                array_map(
                                    fn ($item) => is_array($item) ? data_get($item, 'field_group_id') : null,
                                    $component->getState(),
                                )
                            )
                        );

                        if (count($existingFieldGroupIds) > 0) {
                            $query->whereKeyNot($existingFieldGroupIds);
                        }

                        return $query
                            ->with(['fields'])
                            ->where('active', true);
                    })
                    ->itemStateFromAttachFieldGroupUsing(function (array $data, $form, FieldGroupRepeater $component) use ($getItemStateFromFieldGroup) {
                        $id = $data['recordId'] ?? null;
                        if ($id === null) {
                            return [];
                        }

                        $fieldGroup = $component->getFieldGroupRelationshipQuery()->find($id);

                        return $getItemStateFromFieldGroup($fieldGroup);
                    })
                    ->itemLabel(fn (array $state): ?string => data_get($state, 'field_group_title'))
                    ->loadStateFromRelationshipsUsing(function ($record, FieldGroupRepeater $component) use ($morphFieldGroupsSortColumn, $getItemStateFromFieldGroup) {
                        $records = $component->getRelationship()->with(['fieldGroup'])->orderBy($morphFieldGroupsSortColumn)->get();
                        $state = collect($records)
                            ->pluck('fieldGroup')
                            ->mapWithKeys(fn ($fieldGroup) => [
                                (string) Str::uuid() => $getItemStateFromFieldGroup($fieldGroup),
                            ])
                            ->toArray();
                        $component->state($state);
                    })
                    ->schema([
                        Forms\Components\Hidden::make('field_group_id'),
                        Forms\Components\Hidden::make('field_group_title'),
                        Forms\Components\Hidden::make('field_group_fields'),
                        Forms\Components\Placeholder::make('preview_fields')
                            ->label(__('inspirecms::inspirecms.preview_fields'))
                            ->content(function ($get) {
                                $fieldsData = $get('field_group_fields');

                                if (! $fieldsData) {
                                    return null;
                                }

                                $previewHtmlString = collect($fieldsData)
                                    ->map(fn ($arr) => <<<Html
                                        <div 
                                            class="dark:ring-white/20 gap-1.5 grid grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] items-center mb-4 ring-1 ring-gray-900/10 rounded-md shadow-sm"
                                            style="--cols-default: repeat(3, minmax(0, 1fr)); --cols-lg: repeat(4, minmax(0, 1fr));"
                                        >
                                            <span class="p-4 bg-gray-200 dark:!bg-gray-700 rounded-l-md">
                                                {$arr['type']}
                                            </span>
                                            <span class="p-4">
                                                {$arr['label']}
                                            </span>
                                        </div>
                                    Html)
                                    ->implode('');

                                return new HtmlString($previewHtmlString);
                            }),
                    ]),
            ]);
    }

    public static function detailInfoForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('created_at')->disabled()->inlineLabel(false)
                    ->afterStateHydrated(fn ($state, $component) => $component->state($state ? Carbon::parse($state)->shortRelativeToNowDiffForHumans() : null)),
                Forms\Components\TextInput::make('updated_at')->disabled()->inlineLabel(false)
                    ->afterStateHydrated(fn ($state, $component) => $component->state($state ? Carbon::parse($state)->shortRelativeToNowDiffForHumans() : null)),

                Forms\Components\Toggle::make('is_root_level')
                    ->inlineLabel()
                    ->label(__('inspirecms::inspirecms.is_root_level'))
                    ->validationAttribute(Str::lower(__('inspirecms::inspirecms.is_root_level'))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageTypes::route('/'),
            'create' => Pages\CreatePageType::route('/create'),
            'edit' => Pages\EditPageType::route('/{record}/edit'),
        ];
    }

    public static function getModel(): string
    {
        return config('filament-field-group.models.page_type', CmsPageType::class);
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.page_type');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms::inspirecms.setting');
    }
}
