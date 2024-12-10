<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Content;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Widgets;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Helpers\UIHelper;

use function Filament\Support\generate_search_column_expression;

class PageResource extends BaseContentResource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -9;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Content::class;

    public static function getPermissionPrefixes(): array
    {
        return parent::getBasePermissionPrefixes();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
            'view' => Pages\ViewPage::route('/{record}/view'),
            'trash' => Pages\Trashes::route('/trashes'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIsWebPage();
    }

    //region Global search
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
            attibutes: [
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
        $model = $query->getModel();

        $isForcedCaseInsensitive = static::isGlobalSearchForcedCaseInsensitive();

        /** @var Connection $databaseConnection */
        $databaseConnection = $query->getConnection();

        foreach ($searchAttributes as $searchAttribute) {
            $whereClause = $isFirst ? 'where' : 'orWhere';

            $query->when(
                // Check if the search attribute is a relation column
                str($searchAttribute)->contains('.') &&
                // Check if the search attribute is not an id column
                str($searchAttribute)->afterLast('.') != 'id',
                function (Builder $query) use ($databaseConnection, $isForcedCaseInsensitive, $searchAttribute, $search, $whereClause): Builder {
                    return $query->{"{$whereClause}Relation"}(
                        (string) str($searchAttribute)->beforeLast('.'),
                        generate_search_column_expression((string) str($searchAttribute)->afterLast('.'), $isForcedCaseInsensitive, $databaseConnection),
                        'like',
                        "%{$search}%",
                    );
                },
                fn (Builder $query) => $query->{$whereClause}(
                    generate_search_column_expression($searchAttribute, $isForcedCaseInsensitive, $databaseConnection),
                    'like',
                    "%{$search}%",
                ),
            );

            $isFirst = false;
        }

        return $query;
    }
    //endregion Global search

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.page');
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\ContentPageOverview::class,
        ];
    }
}
