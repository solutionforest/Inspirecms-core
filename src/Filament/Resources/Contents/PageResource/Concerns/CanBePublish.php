<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Concerns;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Enums\PageStatus;
use SolutionForest\InspireCms\Filament\Forms\Components\Actions\ResetAction;

use SolutionForest\InspireCms\Models\CmsContent;
use Throwable;

trait CanBePublish
{
    protected ?string $publishOperation = null;

    protected function getPublishFormAction(string $operation): Action
    {
        if (is_null($operation) || $operation === 'create') {
            $this->publishOperation = 'create';
        } else {
            $this->publishOperation = 'edit';
        }

        return Action::make('publish')
            ->label(__('inspirecms::inspirecms.actions.publish.label'))
            ->modalSubmitActionLabel(__('inspirecms::inspirecms.actions.publish.actions.publish.label'))
            ->keyBindings(['mod+p'])
            ->color('primary')
            ->modalFooterActionsAlignment(Alignment::End)
            ->form(fn (Form $form) => $form->schema([
                static::getPublishedAtComponent(),
            ])->operation('publish'))
            ->beforeFormValidated(function (Action $action) {
                try {

                    $this->validatePublishableData();

                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Please check your form')
                        ->title(__('inspirecms::inspirecms.notification.form_check_error.title'))
                        ->danger()
                        ->send();

                    throw $e;
                }
            })
            ->action(fn (array $data) => $this->publish($data));
    }

    protected function getUnPublishFormAction(string $operation): ?Action
    {
        // Display on edit page only
        if (is_null($operation) || $operation === 'craete') {
            return null;
        }

        return Action::make('unpublish')
            ->label(__('inspirecms::inspirecms.actions.unpublish.label'))
            ->modalSubmitActionLabel(__('inspirecms::inspirecms.actions.unpublish.actions.unpublish.label'))
            ->color('gray')
            ->modalFooterActionsAlignment(Alignment::End)
            // Would't update other data, only change status
            ->action(function (Model|CmsContent|null $record, Action $action) {
                if (is_null($record)) {
                    $action->cancel();
                    return;
                }

                $record->update([
                    'status' => PageStatus::Unpublish->value
                ]);

                $action->success();
            })
            ->successNotification(fn () => $this->getUnpublishedNotification());
    }

    public function publish(array $publishData, bool $shouldRedirect = false): void
    {
        $isCreating = $this->isCreatingPublishableData();

        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $data = $this->getPublishableFormDataBeforePublish(array_merge(
                $publishData,
                ['status' => PageStatus::Publish->value],
            ));

            if ($isCreating) {

                $this->record = $this->handleRecordCreation($data);

                $this->form->model($this->getRecord())->saveRelationships();

                $this->callHook('afterCreate');

            } else {

                $this->handleRecordUpdate($this->getRecord(), $data);

                // Skip save relationships on `getPublishableFormDataBeforePublish`, and handle on this line
                $this->form->model($this->getRecord())->saveRelationships();

                $this->callHook('afterSave');
            }

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

        $this->getPublishedNotification()?->send();

        if ($isCreating) {

            $redirectUrl = $this->getRedirectUrl();

            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));

        } else {

            if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
                $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
            }

        }
    }

    protected function isCreatingPublishableData(): bool
    {
        return $this->publishOperation !== 'edit';
    }

    public function getPublishableFormDataBeforePublish(array $extraData): array
    {
        ray([$this->isCreatingPublishableData(), $this->publishOperation]);
        if ($this->isCreatingPublishableData()) {

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate(array_merge($data, $extraData));

            $this->callHook('beforeCreate');

        } else {

            $this->callHook('beforeValidate');

            // Avoid save relationships before update
            $data = $this->form->getState(shouldCallHooksBefore: false, afterValidate: null);

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave(array_merge($data, $extraData));

            $this->callHook('beforeSave');

        }

        return $data;
    }

    public function validatePublishableData(): void
    {
        $this->form->validate();
    }

    protected function getPublishedNotification(): ?Notification
    {
        $title = $this->getPublishedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    protected function getPublishedNotificationTitle(): ?string
    {
        return __('inspirecms::inspirecms.actions.publish.notifications.published.title');
    }

    protected function getUnpublishedNotification(): ?Notification
    {
        $title = $this->geUnpublishedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    protected function geUnpublishedNotificationTitle(): ?string
    {
        return __('inspirecms::inspirecms.actions.unpublish.notifications.unpublished.title');
    }

    //region Form field(s)/component(s)

    protected static function getPublishedAtComponent(): Forms\Components\Component
    {
        return Forms\Components\DateTimePicker::make('published_at')
            ->label(__('inspirecms::inspirecms.publish_at'))
            ->native(false)
            ->prefixIcon('heroicon-m-calendar-date-range')
            ->suffixAction(ResetAction::make())
            ->hintIcon(
                'heroicon-o-information-circle',
                __('inspirecms::inspirecms.hints.future_publish')
            )
            ->default(now())
            ->required();
    }

    //endregion Form field(s)/component(s)
}
