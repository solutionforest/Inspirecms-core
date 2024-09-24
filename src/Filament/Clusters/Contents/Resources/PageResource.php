<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources;

use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Filament\Clusters\Contents;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;

class PageResource extends BaseContentResource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -9;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Contents::class;

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
            'create-children' => Pages\CreateChildrenPage::route('/{parent}/create-children'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('documentType', fn ($q) => $q->where('is_element_type', false));
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.page');
    }
}
