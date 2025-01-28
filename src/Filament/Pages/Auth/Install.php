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
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Pages\Auth\Concerns\HaveBackgroundImage;
use SolutionForest\InspireCms\InspireCmsConfig;
use Spatie\Permission\Traits\HasRoles;

class Install extends BasePage
{
    use HaveBackgroundImage;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.pages.auth.install';

    /**
     * @var view-string
     */
    protected static string $layout = 'inspirecms::components.layout.split-image-login-page';

    protected static string $slug = 'install';

    protected ?string $maxWidth = '4xl';

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
    }

    public function mount(): void
    {
        if (! InspireCms::needInstall() || Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

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

        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        event(new AuthEvents\Login(Filament::getAuthGuard(), $user, true));

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create($data);

        if ($user) {
            if (is_inspirecms_user($user) || in_array(HasRoles::class, class_uses_recursive($user))) {

                try {
                    // Assign "Admininistrator" role
                    $guardName = InspireCmsConfig::getGuardName();
                    $role = app(config('permission.models.role', \Spatie\Permission\Models\Role::class))::findByName(PermissionManifest::getSuperAdminRoleName(), $guardName);
                    $user->assignRole($role);
                } catch (\Throwable $th) {
                    $this->getAssignRoleFailedNotification()?->send();

                    throw new \Exception('Please ensure you have already run the migration and imported the default data.', previous: $th);
                }

            }
        }

        return $user;
    }

    // region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected function getNameFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::pages/auth/install.form.name.label'))
            ->validationAttribute(__('inspirecms::pages/auth/install.form.email.name'))
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
            ->label(__('inspirecms::pages/auth/install.form.email.label'))
            ->validationAttribute(__('inspirecms::pages/auth/install.form.email.validation_attribute'))
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
            ->label(__('inspirecms::pages/auth/install.form.password.label'))
            ->validationAttribute(__('inspirecms::pages/auth/install.form.password.validation_attribute'))
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
            ->label(__('inspirecms::pages/auth/install.form.password_confirmation.label'))
            ->validationAttribute(__('inspirecms::pages/auth/install.form.email.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }
    // endregion Form field(s)/component(s)

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(__('inspirecms::pages/auth/install.form.actions.register.label'))
            ->submit('register');
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('inspirecms::pages/auth/install.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('inspirecms::pages/auth/install.notifications.throttled') ?: []) ? __('inspirecms::pages/auth/install.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    protected function getAssignRoleFailedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('inspirecms::pages/auth/install.notifications.assign_role_failed.title'))
            ->body(array_key_exists('body', __('inspirecms::pages/auth/install.notifications.assign_role_failed') ?: []) ? __('inspirecms::pages/auth/install.notifications.assign_role_failed.body') : null)
            ->danger();
    }

    public function getTitle(): string | Htmlable
    {
        return __('inspirecms::pages/auth/install.title');
    }

    public function getHeading(): string | Htmlable
    {
        return __('inspirecms::pages/auth/install.heading');
    }

    public function getSubheading(): string | Htmlable
    {
        return __('inspirecms::pages/auth/install.subheading');
    }

    public static function getRouteSlug(): string
    {
        return static::$slug;
    }
}
