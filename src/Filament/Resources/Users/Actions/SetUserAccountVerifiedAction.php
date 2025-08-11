<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\User;

class SetUserAccountVerifiedAction
{
    public static function make()
    {
        return Action::make('setAccountVerified')
            ->label(__('inspirecms::resources/user.buttons.set_account_verified.label'))
            ->requiresConfirmation()
            ->color('success')
            ->link()
            ->size('xs')
            ->icon(FilamentIcon::resolve('inspirecms::verified') ?? 'heroicon-s-check-badge')
            ->successNotificationTitle(__('inspirecms::messages.updated'))
            ->visible(fn ($record) => ! is_null($record) && has_super_admin_role(filament()->auth()->user()) && ! $record->hasVerifiedEmail())
            ->action(fn (User | Model $record) => $record->markEmailAsVerified());
    }
}
