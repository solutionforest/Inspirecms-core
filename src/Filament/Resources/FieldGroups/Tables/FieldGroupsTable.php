<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroups\Tables;

use Carbon\Carbon;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\FieldGroupReplicateForm;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup;

class FieldGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable(false)
            ->modifyQueryUsing(fn ($query) => $query->withCount(['fields', 'documentTypes']))
            ->emptyStateHeading(__('inspirecms::resources/field-group.empty_state.heading'))
            ->emptyStateDescription(__('inspirecms::resources/field-group.empty_state.description'))
            ->emptyStateActions([])
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('title')
            ->columns([

                TextColumn::make('name')
                    ->label(__('inspirecms::resources/field-group.name.label'))
                    ->badge()
                    ->sortable()
                    ->width('1%'),

                TextColumn::make('title')
                    ->label(__('inspirecms::resources/field-group.title.label')),

                TextColumn::make('fields_count')
                    ->label(__('inspirecms::resources/field-group.fields.label'))
                    ->alignEnd()
                    ->width('5%'),

                TextColumn::make('document_types_count')
                    ->label(__('inspirecms::inspirecms.total_xxx_have_used', [
                        'name' => lcfirst(__('inspirecms::inspirecms.document_type.plural')),
                    ]))
                    ->alignEnd()
                    ->width('5%')
                    ->hiddenOn(RelationManager::class),

                // timestamps
                TextColumn::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),

                TextColumn::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->sortable()
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ])
            ->recordActions([

                EditAction::make()
                    ->iconButton(),

                ReplicateAction::make()->iconButton()
                    ->schema(fn (Schema $schema) => FieldGroupReplicateForm::configure($schema))
                    ->excludeAttributes(['fields_count', 'document_types_count'])
                    ->after(function (Model | FieldGroup $replica, Model | FieldGroup $record) {

                        $fields = $record->fields()->get()->map(fn (Model $field) => $field->replicate([
                            'group_id',
                        ])->toArray())->all();

                        $replica->fields()->createMany($fields);
                    }),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->link(),
            ]);
    }
}
