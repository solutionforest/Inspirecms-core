<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Tables;

use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentListTrashPage;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Resources\ContentResource\RelationManagers\ChildrenRelationManager;
use SolutionForest\InspireCms\Filament\Resources\Helpers\ContentResourceHelper;
use SolutionForest\InspireCms\Filament\Tables\Columns\BladeIconColumn;
use SolutionForest\InspireCms\Models\Contracts\Content as ModelsContent;

class ContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function ($query, $livewire) {
                $query->with('publishedVersions');
                if ($livewire instanceof ChildrenRelationManager) {
                    $query->with('parent');
                }

                return $query;
            })
            ->columns([

                BladeIconColumn::make('documentType.icon')
                    ->label('')
                    ->tooltip(fn (Model | ModelsContent $record) => $record->documentType?->title)
                    ->width('1%'),

                TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label(__('inspirecms::resources/content.deleted_at.label'))
                    ->sortable()
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans())
                    ->visibleOn([BaseContentListTrashPage::class])
                    ->width('5%'),

                TextColumn::make('title')
                    ->label(__('inspirecms::resources/content.title.label'))
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->limit(20)->tooltip(fn ($state) => $state),

                TextColumn::make('slug')
                    ->label(__('inspirecms::resources/content.slug.label'))
                    ->searchable(isIndividual: true)
                    ->fontFamily('mono')
                    ->limit(20)->tooltip(fn ($state) => $state),

                TextColumn::make('parent')
                    ->label(__('inspirecms::resources/content.parent.label'))
                    ->getStateUsing(function (Model | ModelsContent $record) {
                        if ($record->isRootLevel()) {
                            return null;
                        }

                        return $record->parent?->title ?? $record->parent_id;
                    })
                    ->grow()
                    ->toggleable(),

                ColumnGroup::make(__('inspirecms::resources/content.visibility.label'))
                    ->columns([

                        TextColumn::make('display_status')
                            ->label(__('inspirecms::resources/content.status.label'))
                            ->formatStateUsing(fn (?ContentStatusOption $state) => $state->getLabel())
                            ->color(fn (?ContentStatusOption $state) => $state->getColor())
                            ->icon(fn (?ContentStatusOption $state) => $state->getIcon())
                            ->badge()
                            ->iconPosition(IconPosition::Before)
                            ->width('2%'),

                        IconColumn::make('is_published')
                            ->label(__('inspirecms::resources/content.is_published.label'))
                            ->getStateUsing(fn (Model | ModelsContent $record) => $record->isPublished())
                            ->boolean()
                            ->width('2%')
                            ->trueIcon(FilamentIcon::resolve('inspirecms::visible'))
                            ->falseIcon(FilamentIcon::resolve('inspirecms::invisiable'))
                            ->falseColor('gray')
                            ->alignCenter()->verticallyAlignCenter()
                            ->hiddenOn([BaseContentListTrashPage::class]),

                        TextColumn::make('published_at')
                            ->label(__('inspirecms::resources/content.published_at.label'))
                            ->getStateUsing(fn (Model | ModelsContent $record) => ContentResourceHelper::getLatestPublishTime($record)?->diffForHumans())
                            ->width('5%')
                            ->hiddenOn([BaseContentListTrashPage::class]),
                    ]),

                // timestamps
                TextColumn::make('created_at')
                    ->label(__('inspirecms::resources/content.created_at.label'))
                    ->sortable()
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
                TextColumn::make('updated_at')
                    ->label(__('inspirecms::resources/content.updated_at.label'))
                    ->sortable()
                    ->formatStateUsing(fn (?Carbon $state) => $state?->diffForHumans())
                    ->width('5%'),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->visible(fn (Model | ModelsContent $record) => ! $record->trashed()),
                ViewAction::make()->iconButton(),
                ActionGroup::make([
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn ($livewire) => ! $livewire instanceof BaseContentListTrashPage),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ])->iconButton(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label(__('inspirecms::resources/content.is_published.label'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereIsPublished(condition: true),
                        false: fn (Builder $query) => $query->whereIsPublished(condition: false),
                        blank: fn (Builder $query) => $query,
                    ),
                TernaryFilter::make('is_root_level')
                    ->label(__('inspirecms::resources/content.is_root_level.label'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereIsRoot(condition: true),
                        false: fn (Builder $query) => $query->whereIsRoot(condition: false),
                        blank: fn (Builder $query) => $query,
                    )
                    ->hiddenOn([ChildrenRelationManager::class]),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('documentType')
                            ->label(__('inspirecms::inspirecms.document_type.singular'))
                            ->relationship(name: 'documentType', titleAttribute: 'title'),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible);
    }
}
