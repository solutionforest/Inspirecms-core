<?php

namespace SolutionForest\InspireCms\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Support\Enums\Width;
use Illuminate\Auth\Events as AuthEvents;
use Illuminate\Contracts\Support\Htmlable;
use SolutionForest\InspireCms\Base\Filament\Pages\Concerns\WithBackgroundImageLayout;
use SolutionForest\InspireCms\Exceptions\AccountLockedException;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class Login extends \Filament\Auth\Pages\Login
{
    use WithBackgroundImageLayout;

    protected Width | string | null $maxContentWidth = 'screen-md';

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        try {
            if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {

                // Already handled
                // event(new AuthEvents\Failed(Filament::getAuthGuard(), null, $this->getCredentialsFromFormData($data)));

                $this->throwFailureValidationException();
            }
        } catch (AccountLockedException $th) {
            $this->getAccountIsLockedNotification()->send();

            return null;
        }

        $user = Filament::auth()->user();
        if ($user instanceof FilamentUser) {

            if ($user->is_locked) {
                Filament::auth()->logout();

                $this->getAccountIsLockedNotification()->send();

                return null;

            }
        }

        event(new AuthEvents\Login(Filament::getAuthGuard(), $user, true));

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label(__('inspirecms::pages/auth/login.form.email.label'));
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->hint(filament()->hasPasswordReset() ? UIHelper::generateLink(text: __('inspirecms::pages/auth/login.buttons.request_password_reset.label'), link: filament()->getRequestPasswordResetUrl(), attributes: ['tabindex' => 3]) : null)
            ->label(__('inspirecms::pages/auth/login.form.password.label'));
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label(__('inspirecms::pages/auth/login.form.remember.label'))
            ->default(true);
    }

    public function registerAction(): Action
    {
        return parent::registerAction()
            ->label(__('inspirecms::pages/auth/login.buttons.register.label'));
    }

    public function getTitle(): string | Htmlable
    {
        return __('inspirecms::pages/auth/login.title');
    }

    public function getHeading(): string | Htmlable
    {
        return __('inspirecms::pages/auth/login.heading');
    }

    public function getSubheading(): string|Htmlable|null
    {
        $base = parent::getSubheading();

        return str(__('inspirecms::pages/auth/login.subheading'))
            ->when(
                (
                    ($base || filled($this->userUndertakingMultiFactorAuthentication))
                    && ((filament()->hasRegistration() && InspireCmsConfig::get('admin.allow_registration', false)) || inspirecms()->needInstall())
                ), 
                fn ($string) => $string->finish('<br/>' . $base)
            )
            ->toHtmlString();
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label(__('inspirecms::pages/auth/login.buttons.authenticate.label'));
    }

    protected function getAccountNotVerifiedNotification(): Notification
    {
        return Notification::make()
            ->title(__('inspirecms::resources/user.notification.account_not_verified.title'))
            ->body(__('inspirecms::resources/user.notification.account_not_verified.body'))
            ->danger();
    }

    protected function getAccountIsLockedNotification(): Notification
    {
        return Notification::make()
            ->title(__('inspirecms::resources/user.notification.account_is_locked.title'))
            ->body(__('inspirecms::resources/user.notification.account_is_locked.body'))
            ->danger();
    }
}
