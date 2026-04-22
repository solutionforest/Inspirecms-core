<?php

namespace SolutionForest\InspireCms\Base\Filament\Pages\Concerns;

use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Concerns\HasMaxWidth;
use Filament\Pages\Concerns\HasTopbar;
use Filament\Panel;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use SolutionForest\InspireCms\Base\Enums\UserActivity;
use SolutionForest\InspireCms\Facades\LocalizationManager;
use SolutionForest\InspireCms\Filament\Forms\Components\UserRolePicker;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\User;

trait ProfilePageTrait
{
    use HasMaxWidth;
    use HasTopbar;

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                $this->getNameFormComponent(),
                                $this->getEmailFormComponent(),
                                $this->getPreferredLanguageFormComponent(),
                            ]),
                        Section::make()
                            ->schema([
                                $this->getRolesFormComponent(),
                            ]),
                    ])
                    ->columnSpan(2),
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                $this->getAvatarFormComponent(),
                            ]),
                        Section::make()
                            ->schema([
                                $this->getPasswordFormComponent(),
                                $this->getPasswordConfirmationFormComponent()
                                    ->visible(fn ($get): bool => filled($get('password'))),
                            ]),
                        $this->getUserActivityDisplayFormComponent(),
                    ])
                    ->columns(1)
                    ->columnSpan(1),
            ]);
    }

    // region Form field(s)/component(s)
    /** @return Field|Component */
    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('inspirecms::resources/user.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    /** @return Field|Component */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('inspirecms::resources/user.email.label'))
            ->validationAttribute(__('inspirecms::resources/user.email.validation_attribute'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true);
    }

    /** @return Field|Component */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
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

    /** @return Field|Component */
    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('inspirecms::resources/user.password_confirmation.label'))
            ->validationAttribute(__('inspirecms::resources/user.password_confirmation.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    /**
     * @return Field|Component
     */
    protected function getPreferredLanguageFormComponent()
    {
        return Select::make('preferred_language')
            ->label(__('inspirecms::resources/user.preferred_language.label'))
            ->validationAttribute(__('inspirecms::resources/user.preferred_language.validation_attribute'))
            ->options(LocalizationManager::getLocaleLabelsFor(LocalizationManager::getUserPreferredLocales()))
            ->searchable()
            ->required();
    }

    /**
     * @return Field|Component
     */
    protected function getRolesFormComponent()
    {
        return UserRolePicker::make('roles')
            ->label(__('inspirecms::resources/user.roles.label'))
            ->validationAttribute(__('inspirecms::resources/user.roles.validation_attribute'));
    }

    /**
     * @return Field|Component
     */
    protected function getAvatarFormComponent()
    {
        return FileUpload::make('avatar')
            ->label(__('inspirecms::resources/user.avatar.label'))
            ->validationAttribute(__('inspirecms::resources/user.avatar.validation_attribute'))
            ->disk(InspireCmsConfig::get('media.user_avatar.driver', 'public'))
            ->directory(InspireCmsConfig::get('media.user_avatar.directory', 'avatars'))
            ->image();
    }

    /**
     * @return Field|Component
     */
    protected function getUserActivityDisplayFormComponent()
    {
        return Section::make()
            ->columns(1)
            ->visibleOn(['edit', 'view'])
            ->inlineLabel()
            ->schema([
                Placeholder::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->content(fn (User | Model $record) => UIHelper::generateCopyableText($record->getKey())),
                Placeholder::make('uuid')
                    ->label(__('inspirecms::inspirecms.uuid'))
                    ->content(fn (User | Model $record) => UIHelper::generateCopyableText($record->uuid)),
                Placeholder::make('last_logged_in_at')
                    ->label(__('inspirecms::resources/user.last_logged_in_at.label'))
                    ->content(fn (User | Model $record) => $record->last_logged_in_at),

                Actions::make([
                    Action::make('resetLockout')
                        ->label(__('inspirecms::resources/user.buttons.reset_lockout.label'))
                        ->requiresConfirmation()
                        ->color('gray')
                        ->link()
                        ->size('xs')
                        ->icon(FilamentIcon::resolve('inspirecms::reset'))
                        ->action(fn (User | Model $record) => $record->handleActivity(UserActivity::LockoutReset))
                        ->visible(fn (User | Model $record) => has_super_admin_role(filament()->auth()->user()) && $record->is_locked),
                ])->alignEnd(),

                Placeholder::make('failed_login_attempt')
                    ->label(__('inspirecms::resources/user.failed_login_attempt.label'))
                    ->content(fn (User | Model $record) => str("<b>{$record->failed_login_attempt}</b>/" . AuthHelper::maxAttempts())->toHtmlString()),
                Placeholder::make('last_lockouted_at')
                    ->label(__('inspirecms::resources/user.last_lockouted_at.label'))
                    ->content(fn (User | Model $record) => UIHelper::generateTextWithDescription(
                        text: UIHelper::generateTooltip(text: $record->last_lockouted_at, tooltip: $record->last_lockouted_at?->diffForHumans())->toHtml(),
                        description: $record->locked_until ? UIHelper::generateTooltip(text: __('inspirecms::resources/user.last_lockouted_at.hints', ['time' => $record->locked_until]), tooltip: $record->locked_until?->diffForHumans())->toHtml() : '',
                    )),

                Actions::make([
                    Action::make('setAccountVerified')
                        ->label(__('inspirecms::resources/user.buttons.set_account_verified.label'))
                        ->requiresConfirmation()
                        ->color('success')
                        ->link()
                        ->size('xs')
                        ->icon(FilamentIcon::resolve('inspirecms::verified') ?? 'heroicon-s-check-badge')
                        ->successNotificationTitle(__('inspirecms::messages.updated'))
                        ->action(fn (User | Model $record) => $record->markEmailAsVerified()),
                    Action::make('resendVerificationEmail')
                        ->label(__('inspirecms::resources/user.buttons.resend_verification_email.label'))
                        ->requiresConfirmation()
                        ->color('gray')
                        ->link()
                        ->size('xs')
                        ->icon(FilamentIcon::resolve('inspirecms::email'))
                        ->successNotificationTitle(__('inspirecms::messages.sent'))
                        ->failureNotificationTitle(__('inspirecms::messages.something_went_wrong'))
                        ->action(function (User | Model $record, Action $action) {
                            try {
                                $record->sendEmailVerificationNotification();
                                $action->success();
                            } catch (\Exception $e) {
                                $action->failure();
                            }
                        }),
                ])->alignEnd()->visible(fn (User | Model $record) => has_super_admin_role(filament()->auth()->user()) && ! $record->hasVerifiedEmail()),
                Placeholder::make('email_confirmed_at')
                    ->label(__('inspirecms::resources/user.email_confirmed_at.label'))
                    ->content(fn (User | Model $record) => $record->email_confirmed_at),

                Placeholder::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->content(fn (User | Model $record) => $record->created_at),
                Placeholder::make('updated_at')
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
