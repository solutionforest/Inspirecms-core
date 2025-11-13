<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Tables;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\Users\Tables\Columns\UserAvatarColumn;
use SolutionForest\InspireCms\Filament\Resources\Users\Tables\Columns\UserEmailColumn;
use SolutionForest\InspireCms\Filament\Resources\Users\Tables\Columns\UserNameColumn;
use SolutionForest\InspireCms\Helpers\SearchHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\Models\Contracts\User;

class UsersAssociationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([

                Grid::make(['default' => 4])
                    ->schema([

                        UserAvatarColumn::make()
                            ->columnSpan(['default' => 1]),

                        Stack::make([
                            UserNameColumn::make(),
                            UserEmailColumn::make(),
                        ])->columnSpan(['default' => 3]),
                    ]),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->slideOver()
                    ->recordSelect(
                        function (Select $select) {

                            $searchColumns = [
                                'name',
                                'email',
                            ];

                            $getOptions = fn (RelationManager $livewire, $search = null) => SearchHelper::getAttachOptions(
                                relationship: $livewire->getRelationship(),
                                inverseRelationshipName: 'roles',
                                optionsLimit: 50,
                                getRecordTitleUsing: function (Model | User $record) {

                                    $avatar = ($record instanceof User ? ($record->getFilamentAvatarUrl() ?? $record->getFilamentFallbackAvatarUrl()) : null) ?? '';
                                    $name = $record->getFilamentName();

                                    $avatarHtml = UIHelper::generateCircularImage($avatar ?? '', $name, ['ctn' => ['class' => 'flex-shrink-0 w-8 h-8']]);
                                    $text = UIHelper::generateTextWithDescription($name, $record->email);

                                    return str($avatarHtml)
                                        ->append($text)
                                        ->wrap('<div class="flex items-center gap-2">', '</div')
                                        ->toString();
                                },
                                search: $search,
                                searchColumns: $searchColumns,
                            );

                            return $select
                                ->allowHtml()
                                ->options(fn ($livewire) => $getOptions($livewire))
                                ->getSearchResultsUsing(fn ($search, $livewire) => $getOptions($livewire, $search));
                        }
                    )
                    ->multiple(),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
