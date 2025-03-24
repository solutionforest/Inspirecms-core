<?php

namespace SolutionForest\InspireCms\Filament\Resources\RoleResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Helpers\SearchHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\Models\Contracts\User;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $icon = 'heroicon-o-users';

    public function table(Table $table): Table
    {
        return $table
            ->inverseRelationship('roles')
            ->columns([
                Tables\Columns\Layout\Grid::make(['default' => 4])
                    ->schema([
                        Tables\Columns\ImageColumn::make('avatar')
                            ->label(' ')
                            ->circular()
                            ->getStateUsing(fn (User $record) => $record->getFilamentAvatarUrl() ?? filament()->getUserAvatarUrl($record))
                            ->columnSpan(['default' => 1]),

                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('name'),
                            Tables\Columns\TextColumn::make('email')->copyable()->icon('heroicon-m-envelope'),
                        ])
                            ->columnSpan(['default' => 3]),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->slideOver()
                    ->recordSelect(
                        function (Forms\Components\Select $select) {

                            $searchColumns = [
                                'name',
                                'email',
                            ];

                            $getOptions = fn ($search = null) => SearchHelper::getAttachOptions(
                                relationship: $this->getRelationship(),
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
                                ->options($getOptions())
                                ->getSearchResultsUsing(fn ($search) => $getOptions($search));
                        }
                    )
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::inspirecms.user');
    }
}
