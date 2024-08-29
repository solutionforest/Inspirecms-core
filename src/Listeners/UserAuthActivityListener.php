<?php

namespace SolutionForest\InspireCms\Listeners;

use Closure;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Models\CmsUser;

class UserAuthActivityListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the login event.
     *
     * @return void
     */
    public function login(Login $event)
    {
        if (! is_inspirecms_user($event->user)) {
            return;
        }

        $this->wrapInDatabaseTransaction(function () use ($event) {

            /** @var Model|CmsUser */
            $user = $event->user;

            $user->updateQuietly(['last_logged_in_at' => now()]);

            $user->userActivity()->updateOrCreate([
                'ip_address' => request()->ip(),
            ], [
                'last_logged_in_at_utc' => now()->utc(),
            ]);
        });
    }

    /**
     * Handle the logout event.
     *
     * @return void
     */
    public function logout(Logout $event)
    {
        if (is_null($event->user) || ! is_inspirecms_user($event->user)) {
            return;
        }

        $this->wrapInDatabaseTransaction(function () use ($event) {

            /** @var Model|CmsUser */
            $user = $event->user;


            $user->userActivity()->updateOrCreate([
                'ip_address' => request()->ip(),
            ], [
                'last_logged_out_at_utc' => now()->utc(),
            ]);
        });
    }

    /**
     * Handle the failed event.
     *
     * @return void
     */
    public function loginFailed(Failed $event)
    {
        if (blank($event->guard)) {
            return;
        }

        /** @var Model|CmsUser|null */
        $user = null;

        if (is_null($event->user)) {
            
            /** @var SessionGuard $authGuard */
            $authGuard = Auth::guard($event->guard);

            /** @var EloquentUserProvider $provider */
            $provider = $authGuard->getProvider();

            /** @var Model|CmsUser|null */
            $user = $provider->getModel()::query()
                ->where('email', $event->credentials['email'] ?? null)
                ->first();

        } else {
            $user = $event->user;
        }

        $this->wrapInDatabaseTransaction(function () use ($event, $user) {

            if (is_null($user) || ! is_inspirecms_user($user)) {
                return;
            }

            $failedLoginAttempt = $user->failed_login_attempt ?? 0;
            $failedLoginAttempt += 1;

            // TODO: put at config && put this logic on Login page
            if ($failedLoginAttempt >= 5) {
                $user->last_lockouted_at = now();
            }

            $user->failed_login_attempt = $failedLoginAttempt;

            $user->saveQuietly();
        });
    }

    /**
     * Handle the passwordReset event.
     *
     * @return void
     */
    public function passwordReset(PasswordReset $event)
    {
        if (is_null($event->user) || ! is_inspirecms_user($event->user)) {
            return;
        }

        $this->wrapInDatabaseTransaction(function () use ($event) {
            /** @var Model|CmsUser */
            $user = $event->user;
            $user->updateQuietly([
                'last_password_change_date' => now(),
            ]);
        });
    }

    protected function rollBackDatabaseTransaction(): void
    {
        DB::rollBack();
    }

    protected function wrapInDatabaseTransaction(Closure $callback): mixed
    {
        /** @phpstan-ignore-next-line */
        return DB::transaction($callback);
    }
}
