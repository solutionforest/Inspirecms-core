<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources;

use Filament\Resources\Resource;
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
            ->contentGrid(['default' => 3, 'md' => 2, 'sm' => 1])
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->getStateUsing(fn (User $record) => $record->avatar),
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->sortable()->width('1%'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::inspirecms.name'))
                    ->sortable()->width('1%'),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('inspirecms::inspirecms.email')),

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
