<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroups\FieldGroupResource as BaseResource;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\FieldGroupResource\Pages\CreateFieldGroup;
use SolutionForest\InspireCms\Filament\Resources\FieldGroupResource\Pages\EditFieldGroup;
use SolutionForest\InspireCms\Filament\Resources\FieldGroupResource\Pages\ListFieldGroup;
use SolutionForest\InspireCms\Filament\Resources\FieldGroupResource\Pages\ViewFieldGroup;
use SolutionForest\InspireCms\Filament\Resources\FieldGroupResource\RelationManagers\DocumentTypesRelationManager;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\FieldGroupForm;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Tables\FieldGroupsTable;
use SolutionForest\InspireCms\InspireCmsConfig;

class FieldGroupResource extends BaseResource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -9;

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

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return FilamentIcon::resolve('inspirecms::fields');
    }

    public static function form(Schema $schema): Schema
    {
        return FieldGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FieldGroupsTable::configure($table)
            // Avoid delete
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => static::canDelete($record),
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFieldGroup::route('/'),
            'create' => CreateFieldGroup::route('/create'),
            'edit' => EditFieldGroup::route('/{record}/edit'),
            'view' => ViewFieldGroup::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'document_type' => DocumentTypesRelationManager::class,
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
        return __('inspirecms::inspirecms.field_group.singular');
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

        // Load document types if haven't loaded
        if (! $record->relationLoaded('documentTypes')) {
            $record->loadMissing('documentTypes');
        }

        return $record->documentTypes->count() <= 0;
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    // endregion Global search
}
