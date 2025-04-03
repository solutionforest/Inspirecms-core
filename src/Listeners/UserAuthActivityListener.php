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
use SolutionForest\InspireCms\Base\Enums\UserActivity;
use SolutionForest\InspireCms\Exceptions\AccountLockedException;
use SolutionForest\InspireCms\Models\Contracts\User;

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
        /** @var null|Model|User */
        $user = $event->user;

        if (! is_inspirecms_user($user)) {
            return;
        }

        if ($user->is_locked) {
            throw AccountLockedException::user($user);
        }

        $this->wrapInDatabaseTransaction(function () use ($user) {

            $user->handleActivity(UserActivity::Login);

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

            /** @var Model|User */
            $user = $event->user;

            $user->handleActivity(UserActivity::Logout);

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

        /** @var Model|User|null */
        $user = null;

        if (is_null($event->user)) {

            /** @var SessionGuard $authGuard */
            $authGuard = Auth::guard($event->guard);

            /** @var EloquentUserProvider $provider */
            $provider = $authGuard->getProvider();

            /** @var Model|User|null */
            $user = $provider->getModel()::query()
                ->where('email', $event->credentials['email'] ?? null)
                ->first();

        } else {
            $user = $event->user;
        }

        if (is_null($user) || ! is_inspirecms_user($user)) {
            return;
        }

        if ($user->is_locked) {
            throw AccountLockedException::user($user);
        }

        $this->wrapInDatabaseTransaction(function () use ($user) {

            $user->handleActivity(UserActivity::FailedLogin);

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
            /** @var Model|User */
            $user = $event->user;

            $user->handleActivity(UserActivity::PasswordReset);

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
