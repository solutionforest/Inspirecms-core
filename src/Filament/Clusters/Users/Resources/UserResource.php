<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources;

use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Filament\Clusters\Users;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Models\Contracts\User;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class UserResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $cluster = Users::class;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->columns([
                Tables\Columns\Layout\Stack::make([

                    Tables\Columns\ImageColumn::make('avatar_url')
                        ->circular()
                        ->getStateUsing(fn (User $record) => $record->getFilamentAvatarUrl() ?? filament()->getUserAvatarUrl($record)),
                    Tables\Columns\TextColumn::make('name')
                        ->label(__('inspirecms::inspirecms.name'))
                        ->weight(FontWeight::Bold)
                        ->sortable()->width('1%'),
                    Tables\Columns\TextColumn::make('email')
                        ->label(__('inspirecms::inspirecms.email')),
                ]),
            ])
            ->filters([

                // Keywords search
                // Tables\Filters\TrashedFilter
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getUserModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.user');
    }
}
