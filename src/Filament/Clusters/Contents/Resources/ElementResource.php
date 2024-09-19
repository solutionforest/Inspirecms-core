<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Clusters\Contents;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource\Pages;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource\RelationManagers;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\ElementResource\RelationManagers\ChildrenRelationManager;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\RevertOrderGroup;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ElementResource extends BaseContentResource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -8;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $cluster = Contents::class;

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
                                        ]),
                                ]),

                            // Field group grouped component
                            static::getPropertyDataValueComponent(),

                        ])
                        ->grow(),
                ])->revertBreakPoint('lg'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn ($query) => $query->with('parent'))
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable()
                    ->grow(),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label(__('inspirecms::inspirecms.parent'))
                    ->grow(),

                Tables\Columns\ColumnGroup::make(__('inspirecms::inspirecms.visibility'))
                    ->columns([

                        Tables\Columns\TextColumn::make('displayStatus')
                            ->label(__('inspirecms::inspirecms.status'))
                            ->formatStateUsing(fn (?ContentStatusOption $state) => $state->getLabel())
                            ->color(fn (?ContentStatusOption $state) => $state->getColor())
                            ->icon(fn (?ContentStatusOption $state) => $state->getIcon())
                            ->badge()
                            ->iconPosition(IconPosition::Before)
                            ->width('2%'),

                        Tables\Columns\IconColumn::make('is_published')
                            ->label(__('inspirecms::inspirecms.is_published'))
                            ->getStateUsing(fn (Model | Content $record) => $record->isPublished())  // Already include private
                            ->boolean()
                            ->width('2%')
                            ->trueIcon('heroicon-m-eye')
                            ->falseIcon('heroicon-o-eye-slash')
                            ->falseColor('gray')
                            ->alignCenter()->verticallyAlignCenter(),

                        Tables\Columns\TextColumn::make('published_at')
                            ->label(__('inspirecms::inspirecms.publish_at'))
                            ->formatStateUsing(fn (?\Carbon\Carbon $state) => $state?->diffForHumans())
                            ->width('5%'),
                    ]),

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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListElements::route('/'),
            'create' => Pages\CreateElement::route('/create'),
            'edit' => Pages\EditElement::route('/{record}/edit'),
            // 'view' => Pages\ViewPage::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChildrenRelationManager::class,
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
                if ($livewire instanceof ChildrenRelationManager) {
                    $query->where('parent_id', $livewire->getOwnerRecord()?->document_type_id ?? 0);
                } elseif ($operation === 'create') {
                    $query->where('parent_id', 0);
                }
            });
    }

    /**
     * @return Forms\Components\Field | Forms\Components\Component
     */
    protected static function getParentPageFormComponent()
    {
        return parent::getParentPageFormComponent()
            ->disabled()
            ->dehydrated(true)
            ->hidden(function ($operation) {
                return $operation === 'create';
            })
            ->dehydratedWhenHidden(true)
            ->dehydrateStateUsing(function ($livewire, $operation, $record) {
                if ($operation === 'create') {
                    return 0;
                }

                return $record->parent_id;
            });
    }
    //endregion Form field(s)/component(s)
}
