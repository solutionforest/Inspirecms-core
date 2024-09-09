<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentView;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource\Contracts\HasPublishForm;

use function Filament\Support\is_app_url;

class EditPage extends EditRecord implements HasPublishForm
{
    use CanBePublish;

    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            \SolutionForest\InspireCms\Filament\Actions\PreviewContentAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('edit'),
            $this->getSaveFormAction(),
            \Filament\Actions\ActionGroup::make([])
                ->label(__('inspirecms::actions.more_actions.label'))
                ->button()
                ->color('gray')
                ->actions([
                    $this->getUnPublishFormAction('edit'),
                    $this->getSetPrivateFormAction('edit'),
                ]),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::actions.save_draft.label'))
            ->color('secondary');
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        $this->wrapPublisableSavingEventIntoDbTransaction(function () {

            $this->callHook('beforeValidate');

            // Avoid save relationships before update
            $data = $this->form->getState(shouldCallHooksBefore: false, afterValidate: null);
            // $data = $this->form->getState(afterValidate: function () {
            //     $this->callHook('afterValidate');

            //     $this->callHook('beforeSave');
            // });

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $this->handleRecordUpdate($this->getRecord(), $data);

            // Handle save relationships on this line
            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterSave');
        });

        $this->rememberData();

        if ($shouldSendSavedNotification) {
            $this->getSavedNotification()?->send();
        }

        if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }
}
