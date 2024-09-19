<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Filament\Clusters\Contents;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;

class PageResource extends BaseContentResource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -9;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Contents::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'publish',
            'unpublish',
            'set_private',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                RevertOrderGroup::make([

                    Forms\Components\Group::make([

                        Forms\Components\Section::make()
                            ->columns(1)
                            ->schema([
                                static::getSlugFormComponent(),
                                static::getParentPageFormComponent(),
                                static::getTemplateFormComponent(),
                            ]),
                        Forms\Components\Group::make()
                            ->columns(['default' => 1, 'lg' => 1, 'md' => 2])
                            ->visibleOn(['edit', 'view'])
                            ->schema([
                                static::getTimestampsGroupedFormComponent()->columnSpan(1),
                                static::getPublishDetailGroupedFormComponent()->columnSpan(1),
                            ]),

                    ])->grow(false),

                    Forms\Components\Group::make()
                        ->schema([

                            Forms\Components\Section::make()
                                ->columnSpanFull()
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->columnSpanFull()
                                        ->schema([
                                            static::getTitleFormComponent(),
                                        ]),
                                    Forms\Components\Grid::make(['default' => 4])
                                        ->columnSpanFull()
                                        ->schema([

                                            static::documentTypeSelectComponent()->columnSpan(3),
                                            static::getDisplayIsRootLevelFormComponent()->columnSpan(1),
                                        ]),
                                ]),

                            // Field group grouped component
                            static::getPropertyDataValueComponent(),

                        ])
                        ->grow(),
                ])->revertBreakPoint('lg'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
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

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field | Forms\Components\Select
     */
    protected static function documentTypeSelectComponent()
    {
        return parent::documentTypeSelectComponent()
            ->relationship(name: 'documentType', titleAttribute: 'name', modifyQueryUsing: function ($query) {
                return $query->where('is_element_type', false);
            });
    }
    //endregion Form field(s)/component(s)
}
