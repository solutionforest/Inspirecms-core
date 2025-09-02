<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Enums\UserActivity;
use SolutionForest\InspireCms\Models\Contracts\User;

class ResetUserLockoutAction
{
    public static function make()
    {
        return Action::make('resetLockout')
            ->label(__('inspirecms::resources/user.buttons.reset_lockout.label'))
            ->requiresConfirmation()
            ->color('gray')
            ->link()
            ->size('xs')
            ->icon(FilamentIcon::resolve('inspirecms::reset'))
            ->visible(function (null | User | Model $record) {
                if (is_null($record)) {
                    return false;
                }

                return has_super_admin_role(filament()->auth()->user()) && $record->is_locked;
            })
            ->action(fn (User | Model $record) => $record->handleActivity(UserActivity::LockoutReset));
    }
}
