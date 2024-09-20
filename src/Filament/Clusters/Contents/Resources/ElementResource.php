<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources;

use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Base\Filament\RelationManagers\BaseContentChildrenRelationManager;
use SolutionForest\InspireCms\Filament\Clusters\Contents;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;

class ElementResource extends BaseContentResource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -8;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Contents::class;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListElements::route('/'),
            'create' => Pages\CreateElement::route('/create'),
            'edit' => Pages\EditElement::route('/{record}/edit'),
            // 'view' => Pages\ViewPage::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('documentType', fn ($q) => $q->where('is_element_type', true));
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.element');
    }

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Select
     */
    protected static function documentTypeSelectComponent()
    {
        return parent::documentTypeSelectComponent()
            ->relationship(name: 'documentType', titleAttribute: 'name', modifyQueryUsing: function ($query, $livewire, $operation) {
                $query->where('is_element_type', true);
                if ($livewire instanceof BaseContentChildrenRelationManager) {
                    $query->where('parent_id', $livewire->getOwnerRecord()?->document_type_id ?? 0);
                } elseif ($operation === 'create') {
                    $query->where('parent_id', 0);
                }
            });
    }
    //endregion Form field(s)/component(s)
}
