<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroupResource as BaseResource;
use SolutionForest\FilamentFieldGroup\Models\Contracts\FieldGroup;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Filament\Forms\Components\TimestampsGroup;
use SolutionForest\InspireCms\Filament\Resources\Helpers\FieldGroupResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class FieldGroupResource extends BaseResource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -9;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Settings::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'attach',
            'detach',
            'reorder',
            'replicate',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([

                RevertOrderGroup::make([

                    Forms\Components\Section::make()
                        ->columns(1)
                        ->schema([
                            TimestampsGroup::make()->columns(['default' => 1]),
                        ])
                        ->hidden(fn ($operation) => $operation == 'create')
                        ->grow(false),

                    Forms\Components\Section::make()
                        ->columns(2)
                        ->schema([
                            FieldGroupResourceHelper::getTitleFormComponent()->autofocus(true),
                            FieldGroupResourceHelper::getNameFormComponent()->autofocus(false),
                            FieldGroupResourceHelper::getActiveFormComponent(),
                        ])
                        ->grow(),
                ])->revertBreakPoint('lg'),

                FieldGroupResourceHelper::getFieldsFormComponent(),
            ]);
    }

    public static function cloneForm(Form $form): Form
    {
        return $form->schema([
            FieldGroupResourceHelper::getNameFormComponent()->autofocus(false),
            FieldGroupResourceHelper::getTitleFormComponent()->autofocus(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->reorderable(false)
            ->modifyQueryUsing(fn ($query) => $query->withCount(['fields', 'documentTypes']))
            ->emptyStateActions([])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::inspirecms.name'))
                    ->sortable()->width('1%')
                    ->badge(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title')),
                Tables\Columns\TextColumn::make('fields_count')
                    ->label(__('inspirecms::inspirecms.fields'))
                    ->alignEnd()
                    ->width('5%'),
                Tables\Columns\TextColumn::make('document_types_count')
                    ->label(__('inspirecms::inspirecms.total_xxx_have_used', [
                        'name' => __('inspirecms::inspirecms.document_type'),
                    ]))
                    ->alignEnd()
                    ->width('5%'),

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
            // Sync action formats
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\ReplicateAction::make()->iconButton()
                    ->slideOver()
                    ->modalWidth('5xl')
                    ->form(fn (Form $form) => static::cloneForm($form))
                    ->excludeAttributes(['fields_count', 'document_types_count'])
                    ->after(function (Model | FieldGroup $replica, Model | FieldGroup $record) {
                        
                        $fields = $record->fields()->get()->map(fn (Model $field) => $field->replicate([
                            'group_id',
                        ])->toArray())->all();

                        $replica->fields()->createMany($fields);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ])
            // Avoid delete
            ->checkIfRecordIsSelectableUsing(
                fn (Model | FieldGroup $record): bool => static::canDelete($record),
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFieldGroup::route('/'),
            'create' => Pages\CreateFieldGroup::route('/create'),
            'edit' => Pages\EditFieldGroup::route('/{record}/edit'),
            'view' => Pages\ViewFieldGroup::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'document_type' => RelationManagers\DocumentTypesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                // Delete event checking
                'documentTypes',
            ]);
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getFieldGroupModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.field_group');
    }

    public static function getRecordSubTitle(?Model $record): string | Htmlable | null
    {
        return $record?->name ?? null;
    }

    public static function canDelete(Model $record): bool
    {
        if (! parent::canCreate($record)) {
            return false;
        }

        // Load docuemnt types if haven't loaded
        if (! $record->relationLoaded('documentTypes')) {
            $record->loadMissing('documentTypes');
        }

        return $record->documentTypes->count() <= 0;
    }

    //region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    //endregion Global search
}
