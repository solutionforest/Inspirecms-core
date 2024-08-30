<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Filament\Resources\Users\UserResource\Pages;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class UserResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
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

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms::inspirecms.users');
    }
}
