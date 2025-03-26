<?php

namespace SolutionForest\InspireCms\Base\Filament\Pages\Concerns;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Concerns;
use Filament\Panel;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use SolutionForest\InspireCms\Base\Enums\UserActivity;
use SolutionForest\InspireCms\Facades\LocaleManifest;
use SolutionForest\InspireCms\Filament\Forms\Components\UserRolePicker;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\User;

trait ProfilePageTrait
{
    use Concerns\HasMaxWidth;
    use Concerns\HasTopbar;

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                $this->getNameFormComponent(),
                                $this->getEmailFormComponent(),
                                $this->getPreferredLanguageFormComponent(),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                $this->getRolesFormComponent(),
                            ]),
                    ])
                    ->columnSpan(2),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                $this->getAvatarFormComponent(),
                            ]),
                        Forms\Components\Section::make()
                            ->schema([
                                $this->getPasswordFormComponent(),
                                $this->getPasswordConfirmationFormComponent(),
                            ]),
                        $this->getUserActivityDisplayFormComponent(),
                    ])
                    ->columns(1)
                    ->columnSpan(1),
            ]);
    }

    // region Form field(s)/component(s)
    /** @return Forms\Components\Field|Forms\Components\Component */
    protected function getNameFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::resources/user.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    protected function getEmailFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('email')
            ->label(__('inspirecms::resources/user.email.label'))
            ->validationAttribute(__('inspirecms::resources/user.email.validation_attribute'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true);
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    protected function getPasswordFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('password')
            ->label(__('inspirecms::resources/user.password.label'))
            ->validationAttribute(__('inspirecms::resources/user.password.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->autocomplete('new-password')
            ->dehydrated(fn ($state): bool => filled($state))
            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
            ->live(debounce: 500)
            ->same('passwordConfirmation');
    }

    /** @return Forms\Components\Field|Forms\Components\Component */
    protected function getPasswordConfirmationFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('passwordConfirmation')
            ->label(__('inspirecms::resources/user.password_confirmation.label'))
            ->validationAttribute(__('inspirecms::resources/user.password_confirmation.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->visible(fn (Forms\Get $get): bool => filled($get('password')))
            ->dehydrated(false);
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected function getPreferredLanguageFormComponent()
    {
        return Forms\Components\Select::make('preferred_language')
            ->label(__('inspirecms::resources/user.preferred_language.label'))
            ->validationAttribute(__('inspirecms::resources/user.preferred_language.validation_attribute'))
            ->options(LocaleManifest::selectOptions())
            ->searchable()
            ->required();
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected function getRolesFormComponent()
    {
        return UserRolePicker::make('roles')
            ->label(__('inspirecms::resources/user.roles.label'))
            ->validationAttribute(__('inspirecms::resources/user.roles.validation_attribute'))
            ->required();
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected function getAvatarFormComponent()
    {
        return Forms\Components\FileUpload::make('avatar')
            ->label(__('inspirecms::resources/user.avatar.label'))
            ->validationAttribute(__('inspirecms::resources/user.avatar.validation_attribute'))
            ->disk(InspireCmsConfig::get('avatar.driver'))
            ->directory(InspireCmsConfig::get('avatar.directory'))
            ->image();
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected function getUserActivityDisplayFormComponent()
    {
        return Forms\Components\Section::make()
            ->columns(1)
            ->visibleOn(['edit', 'view'])
            ->inlineLabel()
            ->schema([
                Forms\Components\Placeholder::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->content(fn (User | Model $record) => UIHelper::generateCopyableText($record->getKey())),
                Forms\Components\Placeholder::make('last_logged_in_at')
                    ->label(__('inspirecms::resources/user.last_logged_in_at.label'))
                    ->content(fn (User | Model $record) => $record->last_logged_in_at),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('resetLockout')
                        ->label(__('inspirecms::resources/user.buttons.reset_lockout.label'))
                        ->requiresConfirmation()
                        ->color('gray')
                        ->link()
                        ->size('xs')
                        ->icon(FilamentIcon::resolve('inspirecms::reset'))
                        ->action(fn (User | Model $record) => $record->handleActivity(UserActivity::LockoutReset))
                        ->visible(fn (User | Model $record) => has_super_admin_role(filament()->auth()->user()) && $record->is_locked),
                ])->alignEnd(),

                Forms\Components\Placeholder::make('failed_login_attempt')
                    ->label(__('inspirecms::resources/user.failed_login_attempt.label'))
                    ->content(fn (User | Model $record) => str("<b>{$record->failed_login_attempt}</b>/".AuthHelper::maxAttempts())->toHtmlString()),
                Forms\Components\Placeholder::make('last_lockouted_at')
                    ->label(__('inspirecms::resources/user.last_lockouted_at.label'))
                    ->content(fn (User | Model $record) => UIHelper::generateTextWithDescription(
                        text: UIHelper::generateTooltip(text: $record->last_lockouted_at, tooltip: $record->last_lockouted_at?->diffForHumans())->toHtml(),
                        description: $record->locked_until ? UIHelper::generateTooltip(text: __('inspirecms::resources/user.last_lockouted_at.hints', ['time' => $record->locked_until]), tooltip: $record->locked_until?->diffForHumans())->toHtml() : '',
                    )),
                    
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('resendVerificationEmail')
                        ->label(__('inspirecms::resources/user.buttons.resend_verification_email.label'))
                        ->requiresConfirmation()
                        ->color('gray')
                        ->link()
                        ->size('xs')
                        ->icon(FilamentIcon::resolve('inspirecms::email'))
                        ->successNotificationTitle(__('inspirecms::messages.sent'))
                        ->failureNotificationTitle(__('inspirecms::messages.something_went_wrong'))
                        ->action(function (User | Model $record, Forms\Components\Actions\Action $action) {
                            try {
                                $record->sendEmailVerificationNotification();
                                $action->success();
                            } catch (\Exception $e) {
                                $action->failure();
                            }
                        }),
                ])->alignEnd()->visible(fn (User | Model $record) => ! $record->hasVerifiedEmail()),
                Forms\Components\Placeholder::make('email_confirmed_at')
                    ->label(__('inspirecms::resources/user.email_confirmed_at.label'))
                    ->content(fn (User | Model $record) => $record->email_confirmed_at),

                Forms\Components\Placeholder::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->content(fn (User | Model $record) => $record->created_at),
                Forms\Components\Placeholder::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->content(fn (User | Model $record) => $record->updated_at),
            ]);
    }
    // endregion Form field(s)/component(s)

    // region Form configs
    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }
    // endregion Form configs

    // region Page configs

    public function getLayout(): string
    {
        return static::$layout ?? (static::isSimple() ? 'filament-panels::components.layout.simple' : 'filament-panels::components.layout.index');
    }

    public static function isSimple(): bool
    {
        return false;
    }

    public function getView(): string
    {
        return static::$view ?? 'filament-panels::pages.auth.edit-profile';
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public static function isTenantSubscriptionRequired(Panel $panel): bool
    {
        return false;
    }

    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => $this->hasTopbar(),
            'maxWidth' => $this->getMaxWidth(),
        ];
    }
    // endregion Page configs
}
