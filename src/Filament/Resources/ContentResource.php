<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Closure;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Content;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages\CreateContentRecord;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages\EditContentRecord;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages\ListContentRecords;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages\ListTrashedContentRecords;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages\ViewContentRecord;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\RelationManagers\ChildrenRelationManager;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\Widgets\ContentPageOverview;
use SolutionForest\InspireCms\Filament\Resources\Contents\Schemas\ContentForm;
use SolutionForest\InspireCms\Filament\Resources\Contents\Tables\ContentsTable;
use SolutionForest\InspireCms\Helpers\SearchHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content as ModelsContent;

class ContentResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;
    use Translatable;

    protected static ?int $navigationSort = -9;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Content::class;

    protected static ?string $slug = 'pages';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'force_delete',
            'force_delete_any',
            'publish',
            'unpublish',
            'reorder_children',
            'view_history',
            'set_as_default',
            'lock',
            'rollback_version',
        ];
    }

    public static function getTranslatableLocales(): array
    {
        return array_keys(InspireCms::getAllAvailableLanguages());
    }

    public static function canAccess(): bool
    {
        $cluster = static::getClusterSection();
        $permissionName = ! blank($cluster) ? $cluster::getAccessRightPermissionName() : null;
        if (! blank($permissionName)) {
            return filament()->auth()->user()?->can($permissionName) ?? false;
        }

        return false;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return ContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentsTable::configure($table)
            ->checkIfRecordIsSelectableUsing(
                fn (Model | ModelsContent $record): bool => static::canDelete($record),
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentRecords::route('/'),
            'create' => CreateContentRecord::route('/create'),
            'edit' => EditContentRecord::route('/{record}/edit'),
            'view' => ViewContentRecord::route('/{record}/view'),
            'trash' => ListTrashedContentRecords::route('/trashes'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ChildrenRelationManager::make(),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ContentPageOverview::class,
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getContentModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.page');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'latestNonDraftContentVersion', // To check the content is published or not
            'publishedVersions', // To get published version, and determine is published
            'documentType.templates', // For template use
            'parent', // To get parent title
            'webSetting',
            'sitemap',
        ])
            ->whereHas('documentType', fn ($query) => $query->whereCanBeContent());
    }

    public static function resolveRecordRouteBinding(string | int $key, ?Closure $modifyQuery = null): ?Model
    {
        return app(static::getModel())
            ->resolveRouteBindingQuery(
                static::getEloquentQuery()
                    ->with([
                        'documentType',
                    ])
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ]),
                $key,
                static::getRecordRouteKeyName()
            )
            ->first();
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return app(static::getModel())->getQualifiedKeyName();
    }

    // region Global search
    public static function getGloballySearchableAttributes(): array
    {
        /**
         * @var Model
         */
        $model = app(static::getModel());

        return [$model->qualifyColumn('id'), 'slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return UIHelper::generateTextWithBadge(
            text: static::getRecordTitle($record),
            badgeText: $record->slug,
            attributes: [
                'text' => ['class' => 'flex-1 font-semibold'],
                'badge' => ['class' => 'font-mono'],
            ]
        );
    }

    /**
     * @param  array<string>  $searchAttributes
     */
    protected static function applyGlobalSearchAttributeConstraint(Builder $query, string $search, array $searchAttributes, bool &$isFirst): Builder
    {
        return SearchHelper::globalSearchWithRelation(
            query: $query,
            search: $search,
            searchAttributes: $searchAttributes,
            isForcedCaseInsensitive: static::isGlobalSearchForcedCaseInsensitive(),
            isFirst: $isFirst
        );
    }

    // endregion Global search
}
