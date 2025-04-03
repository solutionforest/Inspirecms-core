<?php

namespace SolutionForest\InspireCms\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BasePage;
use Illuminate\Auth\Events as AuthEvents;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use SolutionForest\InspireCms\Base\Filament\Pages\Concerns\HaveBackgroundImage;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\User;
use Spatie\Permission\Traits\HasRoles;

class Register extends BasePage
{
    use HaveBackgroundImage;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.pages.auth.register';

    /**
     * @var view-string
     */
    protected static string $layout = 'inspirecms::components.layout.split-image-login-page';

    protected ?string $maxWidth = '4xl';

    protected bool $isAlreadyInitialized = false;

    public function boot()
    {
        try {

            // Check database table exists
            $tableName = InspireCmsConfig::getUserTableName();
            if (! Schema::hasTable($tableName)) {
                throw new \Exception("Table {$tableName} does not exist, please run migration.");
            }

        } catch (\Throwable $e) {

            throw $e;
        }

        $this->isAlreadyInitialized = ! inspirecms()->needInstall();
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

        } catch (\Throwable $th) {

            $this->getAssignRoleFailedNotification()?->send();

            throw new \Exception('Please ensure you have already run the migration and imported the default data.', previous: $th);
        }
    }

    // region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected function getNameFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::pages/auth/register.form.name.label'))
            ->validationAttribute(__('inspirecms::pages/auth/register.form.email.name'))
            ->required()
            ->maxLength(255)
            ->default('System');
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected function getEmailFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('email')
            ->label(__('inspirecms::pages/auth/register.form.email.label'))
            ->validationAttribute(__('inspirecms::pages/auth/register.form.email.validation_attribute'))
            ->email()
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->unique($this->getUserModel());
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected function getPasswordFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('password')
            ->label(__('inspirecms::pages/auth/register.form.password.label'))
            ->validationAttribute(__('inspirecms::pages/auth/register.form.password.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(Password::default())
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->same('passwordConfirmation');
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected function getPasswordConfirmationFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('passwordConfirmation')
            ->label(__('inspirecms::pages/auth/register.form.password_confirmation.label'))
            ->validationAttribute(__('inspirecms::pages/auth/register.form.email.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
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

    public function getTitle(): string | Htmlable
    {
        return $this->isAlreadyInitialized ? __('inspirecms::pages/auth/register.title.installed') : __('inspirecms::pages/auth/register.title.not_installed');
    }

    public function getHeading(): string | Htmlable
    {
        return $this->isAlreadyInitialized ? __('inspirecms::pages/auth/register.heading.installed') : __('inspirecms::pages/auth/register.heading.not_installed');
    }

    public function showLoginButton(): bool
    {
        return filament()->hasLogin() && $this->isAlreadyInitialized;
    }
}
