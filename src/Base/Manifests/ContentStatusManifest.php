<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Models\Contracts\Content;

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
                false,
                __('inspirecms::inspirecms.page_status.draft.label'),
                'warning',
                'heroicon-o-pencil',
            ),
            new ContentStatusOption(
                1,
                'publish',
                true,
                __('inspirecms::inspirecms.page_status.publish.label'),
                'success',
                'heroicon-o-check-circle'
            ),
            new ContentStatusOption(
                2,
                'unpublish',
                false,
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
        ];
    }

    //region Helpers
    protected static function handlePublishableRecord($record, $publishableState, $livewire, array $publishableData)
    {
        if (! $livewire instanceof ContentForm) {
            throw new \RuntimeException('The Livewire component must implement ContentForm.');
        }

        if ($livewire instanceof EditRecord) {

            $isSuccess = $livewire->handlePublishableRecord(function () use ($publishableData, $livewire, $publishableState) {

                $data = $livewire->getPublishableFormDataBeforePublish();

                $livewire->handlePublishableRecordCreateOrUpdate($data, $publishableData, false, $publishableState);
            });

            if (! $isSuccess) {
                return false;
            }

        } else {

            $record->setPublishableState($publishableState);

            $record->save();

        }

        return true;
    }

    //endregion Helpers
}
