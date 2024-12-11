<?php

namespace SolutionForest\InspireCms\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BasePage;
use Illuminate\Support\Js;
use SolutionForest\InspireCms\Filament\Pages\Auth\Concerns\ProfilePageTrait;

/**
 * @property Form $form
 */
class EditProfile extends BasePage
{
    use ProfilePageTrait;

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
            ->label(__('inspirecms::actions.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    public function getCancelFormAction(): Action
    {
        return Action::make('back')
            ->label(__('inspirecms::actions.cancel.label'))
            ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Js::from(filament()->getUrl()) . ')')
            ->color('gray');
    }

    protected function getRedirectUrl(): ?string
    {
        // reload with preferred language
        return $this->getUrl();
    }
}
