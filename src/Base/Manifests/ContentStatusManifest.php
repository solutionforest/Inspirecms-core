<?php

namespace SolutionForest\InspireCms\Base\Manifests;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Base\Filament\Contracts\ContentForm;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Helpers\ContentHelper;
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

    /** {@inheritDoc} */
    public function setDefaultValue(int $value): void
    {
        $this->defaultValue = $value;
    }

    /** {@inheritDoc} */
    public function getDefaultValue(): ?int
    {
        return $this->defaultValue;
    }

    /** {@inheritDoc} */
    public function getFormActions(array $excepts = [])
    {
        return $this->options
            ->filter(fn (ContentStatusOption $option) => ! in_array($option->getValue(), $excepts))
            ->map(fn (ContentStatusOption $option) => $option->getFormAction())
            ->filter()
            ->values()
            ->all();
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
                    ->label(__('inspirecms::buttons.unpublish.label'))
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-x-circle')
                    ->modalHeading(__('inspirecms::buttons.unpublish.heading'))
                    ->modalDescription('')
                    ->modalSubmitActionLabel(__('inspirecms::buttons.unpublish.label'))
                    ->successNotificationTitle(__('inspirecms::buttons.unpublish.messages.success.title'))
                    ->action(function (null | Model | Content $record, Action $action, ContentForm $livewire) {
                        if (is_null($record)) {
                            $action->cancel();

                            return;
                        }

                        if (! ContentHelper::handlePublishableRecord($record, 'unpublish', $livewire, [])) {
                            return;
                        }

                        $action->success();

                    })
                    ->authorize('unpublish')
            ),
        ];
    }
}
