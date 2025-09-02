<?php

namespace SolutionForest\InspireCms\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Js;
use SolutionForest\InspireCms\Filament\Resources\Users\Schemas\UserEditForm;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    public function form(Schema $schema): Schema
    {
        return UserEditForm::configure($schema);
    }

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }

    public static function isSimple(): bool
    {
        return false;
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public static function isTenantSubscriptionRequired(Panel $panel): bool
    {
        return false;
    }

    public static function getLabel(): string
    {
        return __('inspirecms::pages/auth/profile.label');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('inspirecms::notification.saved.title');
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('inspirecms::buttons.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    public function getCancelFormAction(): Action
    {
        return Action::make('back')
            ->label(__('inspirecms::buttons.cancel.label'))
            ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Js::from(filament()->getUrl()) . ')')
            ->color('gray');
    }

    protected function getRedirectUrl(): ?string
    {
        // reload with preferred language
        return $this->getUrl();
    }
}
