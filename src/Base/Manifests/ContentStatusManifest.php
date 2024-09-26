<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\BaseContentResource;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\Pages\EditPage;
use SolutionForest\InspireCms\Models\Concerns\Publishable;
use SolutionForest\InspireCms\Models\Contracts\Content;

use function Filament\Support\is_app_url;

class ContentStatusManifest implements ContentStatusManifestInterface
{
    /** @var Collection<ContentStatusOption> */
    protected Collection $options;

    protected int $defaultValue = 0;

    public function __construct()
    {
        $this->options = collect(static::getDefaultOptions());
    }

    /** {@inheritDoc} */
    public function addOption(ContentStatusOption $option, bool $replace = false): void
    {
        $existing = $this->options
            ->where(fn (ContentStatusOption $optionToCheck) => $optionToCheck->getValue(), $option->getValue())
            ->where(fn (ContentStatusOption $optionToCheck) => $optionToCheck->getName(), $option->getName())
            ->first();
        if (! $existing || ($existing && $replace)) {
            $this->options->push($option);
        }
    }

    /** {@inheritDoc} */
    public function replaceOption(ContentStatusOption $option): void
    {
        $this->addOption($option, true);
    }

    /** {@inheritDoc} */
    public function getOption(int | string $valueOrName): ?ContentStatusOption
    {
        if (is_int($valueOrName)) {
            return $this->options
                ->where(fn (ContentStatusOption $option) => $option->getValue() == $valueOrName)
                ->first();
        } else {
            return $this->options
                ->where(fn (ContentStatusOption $option) => $option->getName() == $valueOrName)
                ->first();
        }
    }

    /** {@inheritDoc} */
    public function selectOptions(): Collection
    {
        return $this->options->keyBy('value');
    }

    /** {@inheritDoc} */
    public function all(): Collection
    {
        return $this->options;
    }

    public function setDefaultValue(int $value): void
    {
        $this->defaultValue = $value;
    }

    public function getDefaultValue(): ?int
    {
        return $this->defaultValue;
    }

    protected static function getDefaultOptions(): array
    {
        return [
            new ContentStatusOption(
                0,
                'draft',
                __('inspirecms::inspirecms.page_status.draft.label'),
                'warning',
                'heroicon-o-pencil',
            ),
            new ContentStatusOption(
                1,
                'publish',
                __('inspirecms::inspirecms.page_status.publish.label'),
                'success',
                'heroicon-o-check-circle'
            ),
            new ContentStatusOption(
                2,
                'unpublish',
                __('inspirecms::inspirecms.page_status.unpublish.label'),
                'gray',
                'heroicon-o-x-circle',
                // Must have record injected
                fn () => Action::make('unpublish')
                    ->label(__('inspirecms::actions.unpublish.label'))
                    ->modalSubmitActionLabel(__('inspirecms::actions.unpublish.actions.unpublish.label'))
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (null | Model | Content $record, Action $action, $livewire) {
                        if (is_null($record)) {
                            $action->cancel();

                            return;
                        }

                        if (! static::handlePublishableRecord($record, 'unpublish', $livewire, [])) {
                            return;
                        }

                        $action->success();

                    })
                    ->authorize('unpublish')
                    ->successNotification(
                        fn () => Notification::make()
                            ->success()
                            ->title(__('inspirecms::actions.unpublish.notifications.unpublished.title'))
                    )
            ),
            new ContentStatusOption(
                3,
                'private',
                __('inspirecms::inspirecms.page_status.private.label'),
                'secondary',
                'heroicon-o-lock-closed',
                fn () => Action::make('private')
                    ->label(__('inspirecms::actions.private.label'))
                    ->modalSubmitActionLabel(__('inspirecms::actions.private.actions.private.label'))
                    ->color('gray')
                    ->form(fn (Form $form) => $form->schema([
                        BaseContentResource::getPublishedAtComponent(),
                    ])->operation('publish'))
                    ->beforeFormValidated(function (Action $action, $livewire) {
                        try {

                            if ($livewire instanceof EditPage &&
                                in_array(CanBePublish::class, class_uses_recursive($livewire))) {

                                $livewire->validatePublishableData();

                            }

                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title(__('inspirecms::notification.form_check_error.title'))
                                ->danger()
                                ->send();

                            throw $e;
                        }
                    })
                    ->action(function (Model | Content $record, array $data, Action $action, $livewire) {
                        if (is_null($record)) {
                            $action->cancel();

                            return;
                        }

                        if (! static::handlePublishableRecord($record, 'private', $livewire, $data)) {
                            return;
                        }

                        $action->success();

                        if ($livewire instanceof EditPage) {

                            $redirectUrl = $livewire->getUrl(['record' => $record->getKey()]);

                            $livewire->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
                        }
                    })
                    ->authorize('setPrivate')
                    ->successNotification(
                        fn () => Notification::make()
                            ->success()
                            ->title(__('inspirecms::actions.private.notifications.updated.title'))
                    )
            ),
        ];
    }

    //region Helpers
    protected static function handlePublishableRecord($record, $publishableState, $livewire, array $data)
    {

        if ($livewire instanceof EditRecord &&
            in_array(CanBePublish::class, class_uses_recursive($livewire))) {

            $isSuccess = $livewire->handlePublishableRecord(function () use ($data, $livewire, $publishableState) {

                $data = $livewire->getPublishableFormDataBeforePublish($data);

                $livewire->handlePublishableRecordCreateOrUpdate($data, false, $publishableState);
            });

            if (! $isSuccess) {
                return false;
            }

        } elseif (in_array(Publishable::class, class_uses_recursive($record))) {

            $record->setPrivateUse($data);

        } else {

            $record->setPublishableState($publishableState);

            $record->save();

        }

        return true;
    }

    //endregion Helpers
}
