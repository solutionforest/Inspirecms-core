<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Filament\Forms\Components\DocumentFieldGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Filament\Tables\Actions\CloneAction;
use SolutionForest\InspireCms\Filament\Tables\Actions\QuickEditAction;
use SolutionForest\InspireCms\Models\Contracts\DocumentType as CmsDocumentType;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class DocumentTypeResource extends Resource
{
    protected static ?int $navigationSort = -10;

    protected static ?string $navigationIcon = null;

    protected static ?string $cluster = Settings::class;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                RevertOrderGroup::make([

                    Forms\Components\Section::make()
                        ->columns(1)
                        ->schema([
                            static::getCanUseAtRootFormComponent(),
                            static::getTimestampsGroupedFormComponent(),
                        ])
                        ->grow(false),
                    Forms\Components\Section::make()
                        ->columns(1)
                        ->schema([
                            static::getTitleFormComponent()->inlineLabel()->columnSpanFull(),
                            static::getFieldGroupFormComponent(),
                        ])
                        ->grow(),
                ])->revertBreakPoint('lg'),
            ]);
    }

    public static function quickForm(Form $form): Form
    {
        return $form
            ->schema([
                static::getTitleFormComponent()->inlineLabel(),
                static::getCanUseAtRootFormComponent(),
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
                Tables\Columns\IconColumn::make('can_use_at_root')
                    ->label(__('inspirecms::inspirecms.can_use_at_root'))
                    ->boolean(),

                // timestamps
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\ActionGroup::make([

                    QuickEditAction::make(),

                    CloneAction::make()
                        ->recordTitleAttribute('title')
                        ->saveRelationshipsUsing(function (Model | CmsDocumentType $originalRecord, Model | CmsDocumentType $record) {

                            $fieldGroups = $originalRecord->morphFieldGroups->map(fn (Model $originalFieldGroup) => $originalFieldGroup->replicate([
                                'model_type',
                                'model_id',
                            ])->toArray())->all();

                            $record->morphFieldGroups()->createMany($fieldGroups);
                        }),
                ]),
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
            'index' => Pages\ListDocumentTypes::route('/'),
            'create' => Pages\CreateDocumentType::route('/create'),
            'edit' => Pages\EditDocumentType::route('/{record}/edit'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getDocumentTypeModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.document_type');
    }

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTitleFormComponent()
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::inspirecms.title'))
            ->required();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getFieldGroupFormComponent()
    {
        return DocumentFieldGroup::make()
            ->modifyFieldGroupSelectUsing(function (Forms\Components\Select $select) {
                return $select
                    ->createOptionModalHeading(fn () => __('inspirecms::inspirecms.create_xxx', [
                        'name' => strtolower(__('inspirecms::inspirecms.field_group')),
                    ]))
                    ->createOptionForm(function (Form $form) {

                        $fieldGroupResource = config('inspirecms.resources.field_group', FieldGroupResource::class);

                        if (method_exists($fieldGroupResource, 'quickForm')) {
                            return $fieldGroupResource::quickForm($form);
                        }

                        if (method_exists($fieldGroupResource, 'form')) {
                            return $fieldGroupResource::form($form);
                        }

                        return $form;
                    })
                    ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                        $action
                            ->modalWidth('3xl')
                            ->modalFooterActionsAlignment(Alignment::End);
                    })
                    ->createOptionUsing(function (array $data) {

                        $createdFieldGroup = InspireCmsConfig::getFieldGroupModelClass()::create($data);

                        return $createdFieldGroup->getKey();
                    });
            })
            ->extraFieldGroupRepeaterItemActions([
                Forms\Components\Actions\Action::make('goToEdit')
                    ->icon(fn () => FilamentIcon::resolve('actions::edit-action') ?? 'heroicon-m-pencil-square')
                    ->url(function (array $arguments, Forms\Components\Repeater $component) {

                        $fieldGroupResource = config('inspirecms.resources.field_group', FieldGroupResource::class);

                        $itemData = $component->getItemState($arguments['item']);

                        if (! (
                            $fieldGroupResource::hasPage('edit') ||
                            is_array($itemData) ||
                            isset($itemData['field_group_id'])
                        )
                        ) {

                            return null;
                        }

                        try {

                            return $fieldGroupResource::getUrl('edit', [
                                'record' => $itemData['field_group_id'],
                                'activeRelationManager' => 0,   // fields relations
                            ]);

                        } catch (\Throwable $e) {

                            return null;

                        }
                    }, true)->visible(fn ($action) => ! blank($action->getUrl())),
            ])
            ->inlineLabel();
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getCanUseAtRootFormComponent()
    {
        return Forms\Components\Toggle::make('can_use_at_root')
            ->label(__('inspirecms::inspirecms.can_use_at_root'))
            ->inlineLabel()
            ->default(false);
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getTimestampsGroupedFormComponent()
    {
        return TimestampsGroup::make()
            ->columns(['default' => 1]);
    }
    //endregion Form field(s)/component(s)
}
