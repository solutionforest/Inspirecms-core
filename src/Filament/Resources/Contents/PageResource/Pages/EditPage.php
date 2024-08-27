<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Concerns\CanBePublish;
use Throwable;

class EditPage extends EditRecord
{
    use CanBePublish;

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('save'),
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::inspirecms.actions.save_draft.label'))
            ->color('secondary');
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

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

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        if ($shouldSendSavedNotification) {
            $this->getSavedNotification()?->send();
        }

        if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }
}
