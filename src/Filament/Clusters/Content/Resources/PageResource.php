<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCms\Filament\Clusters\Content;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;

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
        return parent::getEloquentQuery()
            ->whereHas('documentType', fn ($q) => $q->where('is_web_page', true));
    }

    //region Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'slug'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return new HtmlString(Blade::render(<<<'blade'
            <div class="flex gap-x-2 items-center">
                <span class="flex-1 font-semibold">
                    {{ $title }}
                </span>
                <x-filament::badge class="font-mono">
                    {{ $badge }}
                </x-filament::badge>
            </div>
        blade, ['title' => static::getRecordTitle($record), 'badge' => $record->slug]));
    }
    //endregion Global search

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.page');
    }
}
