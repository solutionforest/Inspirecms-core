<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Actions;

use Exception;
use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\User;

class ResendUserVerificationEmailAction
{
    public static function make()
    {
        return Action::make('resendVerificationEmail')
            ->label(__('inspirecms::resources/user.buttons.resend_verification_email.label'))
            ->requiresConfirmation()
            ->color('gray')
            ->link()
            ->size('xs')
            ->icon(FilamentIcon::resolve('inspirecms::email'))
            ->successNotificationTitle(__('inspirecms::messages.sent'))
            ->failureNotificationTitle(__('inspirecms::messages.something_went_wrong'))
            ->visible(fn ($record) => ! is_null($record) && has_super_admin_role(filament()->auth()->user()) && ! $record->hasVerifiedEmail())
            ->action(function (User | Model $record, Action $action) {
                try {
                    $record->sendEmailVerificationNotification();
                    $action->success();
                } catch (Exception $e) {
                    $action->failure();
                }
            });
    }
}
