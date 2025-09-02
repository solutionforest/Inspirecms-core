<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Filament\Resources\Users\Tables\Columns\UserAvatarColumn;
use SolutionForest\InspireCms\Filament\Resources\Users\Tables\Columns\UserEmailColumn;
use SolutionForest\InspireCms\Filament\Resources\Users\Tables\Columns\UserNameColumn;
use SolutionForest\InspireCms\Models\Contracts\User;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                UserAvatarColumn::make(),
                UserNameColumn::make()
                    ->weight(FontWeight::Bold)
                    ->width('1%'),
                TextColumn::make('name')
                    ->label(__('inspirecms::resources/user.name.label'))
                    ->weight(FontWeight::Bold)
                    ->sortable()->width('1%'),
                UserEmailColumn::make(),
                TextColumn::make('roles.name')
                    ->label(__('inspirecms::resources/user.roles.label')),
                TextColumn::make('last_logged_in_at')
                    ->label(__('inspirecms::resources/user.last_logged_in_at.label')),
                IconColumn::make('is_account_verified')
                    ->label(__('inspirecms::resources/user.is_account_verified.label'))
                    ->getStateUsing(fn (User $record) => $record->hasVerifiedEmail())->boolean()
                    ->tooltip(fn (User $record) => $record->email_confirmed_at)
                    ->alignCenter(),
                IconColumn::make('is_locked')
                    ->label(__('inspirecms::resources/user.is_locked.label'))
                    ->boolean(false)
                    ->icon(fn ($state) => $state ? (FilamentIcon::resolve('inspirecms::locked') ?? Heroicon::LockClosed) : Heroicon::XMark)
                    ->tooltip(fn (User $record) => $record->last_lockouted_at?->diffForHumans())
                    ->alignCenter(),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
            ]);
    }
}
