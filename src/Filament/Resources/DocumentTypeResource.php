<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Pages\EditDocumentType;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Pages\ListDocumentTypes;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Pages\ViewDocumentType;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers\AllowingDocumentTypesRelationManager;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers\ContentRelationManager;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers\FieldGroupsRelationManager;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers\TemplatesRelationManager;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\Widgets\AlertOverview;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Schemas\DocumentTypeForm;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Tables\DocumentTypesTable;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class DocumentTypeResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

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
            'replicate',
        ];
    }

    protected static ?int $navigationSort = -10;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Settings::class;

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return FilamentIcon::resolve('inspirecms::document_type');
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentTypes::route('/'),
            'edit' => EditDocumentType::route('/{record}/edit'),
            'view' => ViewDocumentType::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            'field_group' => RelationGroup::make(fn () => __('inspirecms::resources/document-type.tabs.field_groups'), [
                // RelationManagers\InheritedDocumentTypesRelationManager::class,
                FieldGroupsRelationManager::class,
            ])->icon(FilamentIcon::resolve('inspirecms::fields')),
            'templates' => RelationGroup::make(fn () => __('inspirecms::resources/document-type.tabs.templates'), [
                TemplatesRelationManager::class,
            ])->icon(FilamentIcon::resolve('inspirecms::templates')),
            'used_by' => RelationGroup::make(fn () => __('inspirecms::inspirecms.used_by'), [
                ContentRelationManager::class,
                AllowingDocumentTypesRelationManager::class,
                // RelationManagers\InheritingDocumentTypesRelationManager::class,
            ]),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AlertOverview::make(),
        ];
    }

    /**
     * @return class-string<Model & DocumentType>
     */
    public static function getModel(): string
    {
        return InspireCmsConfig::getDocumentTypeModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.document_type.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inspirecms::inspirecms.document_type.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['templates', 'fieldGroups']);
    }

    public static function canDelete(Model $record): bool
    {
        if ($record instanceof DocumentType) {
            return ! ($record->content()->withoutGlobalScopes([SoftDeletingScope::class])->count() > 0);
        }

        return parent::canDelete($record);
    }

    // region Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return UIHelper::generateTextWithBadge(
            text: static::getRecordTitle($record),
            badgeText: $record instanceof DocumentType ? $record->slug : null,
            attributes: [
                'text' => ['class' => 'flex-1 font-semibold'],
                'badge' => ['class' => 'font-mono'],
            ]
        );
    }
    // endregion Global search
}
