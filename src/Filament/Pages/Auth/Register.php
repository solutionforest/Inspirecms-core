<?php

namespace SolutionForest\InspireCms\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Exception;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Support\Enums\Width;
use Illuminate\Auth\Events as AuthEvents;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use SolutionForest\InspireCms\Base\Filament\Pages\Concerns\HaveBackgroundImage;
use SolutionForest\InspireCms\Base\Filament\Pages\Concerns\WithBackgroundImageLayout;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserEmailInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserNameInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserPasswordConfirmInput;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components\UserPasswordInput;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use SolutionForest\InspireCms\Models\Contracts\User;
use Spatie\Permission\Traits\HasRoles;
use Throwable;
use Illuminate\Support\HtmlString;

class Register extends \Filament\Auth\Pages\Register
{
    use WithBackgroundImageLayout;

    protected Width | string | null $maxContentWidth = 'screen-md';

    protected bool $isAlreadyInitialized = false;

    public function boot()
{
        try {

            // Check database table exists
            $tableName = InspireCmsConfig::getUserTableName();
            if (! Schema::hasTable($tableName)) {
                throw new Exception("Table {$tableName} does not exist, please run migration.");
            }

        } catch (Throwable $e) {

            throw $e;
        }

        $this->isAlreadyInitialized = ! inspirecms()->needInstall();

        if ($this->isAlreadyInitialized && ! InspireCmsConfig::get('admin.allow_registration', false)) {
            // If registration is not allowed, redirect to login page
            $loginUrl = filament()->getPanel(InspireCmsConfig::getPanelId())?->getLoginUrl();
            if ($loginUrl) {
                return redirect()->intended($loginUrl);
            } else {
                throw new Exception('Registration is not allowed, and no login URL is configured.');
            }
        }
    }

    public function mount(): void
    {
        // if (Filament::auth()->check()) {
        //     redirect()->intended(Filament::getUrl());
        // }

        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }

    public function register(): ?RegistrationResponse
    {
        if (! app(LicenseManager::class)->canCreateUser()) {
            $this->getLicenseLimitNotification()?->send();

            return null;
        }

        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        event(new AuthEvents\Registered($user));

        // handle by event
        // $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        event(new AuthEvents\Login(Filament::getAuthGuard(), $user, true));

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create($data);

        if ($user) {

            $isFirstCreatedUser = $this->getUserModel()::count() === 1;

            if (
                (is_inspirecms_user($user) || in_array(HasRoles::class, class_uses_recursive($user))) &&
                $isFirstCreatedUser
            ) {
                $this->assignSuperAdminRoleToUser($user);
            }
        }

        return $user;
    }

    /**
     * @param  Model  $user
     */
    protected function assignSuperAdminRoleToUser($user)
    {
        try {
            // Assign "Admininistrator" role
            $guardName = AuthHelper::guardName();
            $roleClass = InspireCmsConfig::getRoleModelClass();

            $role = $roleClass::findByName(PermissionManifest::getSuperAdminRoleName(), $guardName);

            $user->assignRole($role);

            if ($user instanceof User) {
                $user->markEmailAsVerified();
            }

        } catch (Throwable $th) {

            $this->getAssignRoleFailedNotification()?->send();

            throw new Exception('Please ensure you have already run the migration and imported the default data.', previous: $th);
        }
    }

    // region Form field(s)/component(s)
    /**
     * @return Field|\Filament\Schemas\Components\Component
     */
    protected function getNameFormComponent(): Component
    {
        return UserNameInput::make()
            ->label(__('inspirecms::pages/auth/register.form.name.label'))
            ->validationAttribute(__('inspirecms::pages/auth/register.form.email.name'))
            ->required()
            ->default('System');
    }

    /**
     * @return Field|\Filament\Schemas\Components\Component
     */
    protected function getEmailFormComponent(): Component
    {
        return UserEmailInput::make()
            ->label(__('inspirecms::pages/auth/register.form.email.label'))
            ->validationAttribute(__('inspirecms::pages/auth/register.form.email.validation_attribute'))
            ->autofocus()
            ->unique($this->getUserModel());
    }

    /**
     * @return Field|\Filament\Schemas\Components\Component
     */
    protected function getPasswordFormComponent(): Component
    {
        return UserPasswordInput::make()
            ->label(__('inspirecms::pages/auth/register.form.password.label'))
            ->validationAttribute(__('inspirecms::pages/auth/register.form.password.validation_attribute'))
            ->required();
    }

    /**
     * @return Field|\Filament\Schemas\Components\Component
     */
    protected function getPasswordConfirmationFormComponent(): Component
    {
        return UserPasswordConfirmInput::make()
            ->label(__('inspirecms::pages/auth/register.form.password_confirmation.label'))
            ->validationAttribute(__('inspirecms::pages/auth/register.form.email.validation_attribute'));
    }
    // endregion Form field(s)/component(s)

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(__('inspirecms::pages/auth/register.buttons.register.label'))
            ->submit('register');
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label(__('inspirecms::pages/auth/register.buttons.login.label'))
            ->url(filament()->getLoginUrl());
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('inspirecms::pages/auth/register.messages.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('inspirecms::pages/auth/register.messages.throttled') ?: []) ? __('inspirecms::pages/auth/register.messages.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    protected function getAssignRoleFailedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('inspirecms::pages/auth/register.messages.assign_role_failed.title'))
            ->body(array_key_exists('body', __('inspirecms::pages/auth/register.messages.assign_role_failed') ?: []) ? __('inspirecms::pages/auth/register.messages.assign_role_failed.body') : null)
            ->danger();
    }

    protected function getLicenseLimitNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('inspirecms::pages/auth/register.messages.license_limit_exceeded.title'))
            ->body(__('inspirecms::pages/auth/register.messages.license_limit_exceeded.body'))
            ->danger();
    }

    public function getTitle(): string | Htmlable
    {
        return $this->isAlreadyInitialized ? __('inspirecms::pages/auth/register.title.installed') : __('inspirecms::pages/auth/register.title.not_installed');
    }

    public function getHeading(): string | Htmlable
    {
        return $this->isAlreadyInitialized ? __('inspirecms::pages/auth/register.heading.installed') : __('inspirecms::pages/auth/register.heading.not_installed');
    }

    public function getSubheading(): string | Htmlable | null
    {
        if (! $this->showLoginButton()) {
            return null;
        }

        return parent::getSubheading();
    }

    public function showLoginButton(): bool
    {
        return filament()->hasLogin() && $this->isAlreadyInitialized;
    }
}
