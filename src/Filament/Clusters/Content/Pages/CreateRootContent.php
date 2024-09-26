<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Js;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Filament\Clusters\Content;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CreateContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\HasPublishForm;
use Throwable;

use function Filament\Support\is_app_url;

/**
 * @property Form $form
 */
class CreateRootContent extends Page implements HasPublishForm
{
    use CanBePublish;
    use CreateContentPageTrait;
    use WithPagination;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.clusters.contents.create';

    protected static ?string $slug = 'create';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = Content::class;

    public function mount(): void
    {
        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function authorizeAccess(): void
    {
        if ($cluster = $this->getCluster()) {
            abort_unless($cluster::canAccess(), 403);
        }
    }

    public function create(): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeCreate($data);

            $this->callHook('beforeCreate');

            $this->record = $this->handleRecordCreation($data);

            $this->form->model($this->getRecord())->saveRelationships();

            $this->callHook('afterCreate');

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

        $this->getCreatedNotification()?->send();

        $redirectUrl = $this->getRedirectUrl();

        $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
    }

    protected function getCreatedNotification(): ?Notification
    {
        $title = $this->getCreatedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->getCreatedNotificationMessage() ?? __('filament-panels::resources/pages/create-record.notifications.created.title');
    }

    /**
     * @deprecated Use `getCreatedNotificationTitle()` instead.
     */
    protected function getCreatedNotificationMessage(): ?string
    {
        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);

        // if (
        //     static::getResource()::isScopedToTenant() &&
        //     ($tenant = Filament::getTenant())
        // ) {
        //     return $this->associateRecordWithTenant($record, $tenant);
        // }

        $record->save();

        return $record;
    }

    // protected function associateRecordWithTenant(Model $record, Model $tenant): Model
    // {
    //     $relationship = static::getResource()::getTenantRelationship($tenant);

    //     if ($relationship instanceof HasManyThrough) {
    //         $record->save();

    //         return $record;
    //     }

    //     return $relationship->save($record);
    // }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    public function getTitle(): string | Htmlable
    {
        if (filled(static::$title)) {
            return static::$title;
        }

        return __('inspirecms::pages/create-root-content.title');
    }

    public function getBreadcrumb(): string
    {
        return __('inspirecms::pages/create-root-content.breadcrumb');
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs[] = $this->getBreadcrumb();

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    public function form(Form $form): Form
    {
        return static::getContentResource()::form($form);
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('create', $this->getModel()),
            $this->getSubmitFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSubmitFormAction(): Action
    {
        return Action::make('create')
            ->label(__('inspirecms::actions.save.label'))
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('filament-panels::resources/pages/create-record.form.actions.cancel.label'))
            ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = ' . Js::from($this->previousUrl ?? static::getResource()::getUrl()) . ')')
            ->color('gray');
    }

    public function getSubNavigation(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        if ($cluster = $this->getCluster()) {
            return $cluster::getUrl();
        }

        return $this->getUrl();
    }
}
