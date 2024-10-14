<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class MediaPicker extends Field
{
    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.media-picker.index';

    protected array $mimeTypes = ['*'];

    protected bool $multiple = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (MediaPicker $component, $state) {
            if (! is_array($state)) {
                $state = is_null($state) || empty($state) ? [] : [$state];
            } elseif ($component->isMultiple()) {
                $state = array_filter($state);
            } elseif (! $component->isMultiple() && count(array_filter($state)) > 0) {
                $state = array_filter([$state[0]]);
            } else {
                $state = [];
            }
            $component->state($state);
        });

        $this->registerActions([
            $this->getSelectAction(),
            $this->getClearAction(),
        ]);
    }

    public function image(): static
    {
        return $this->mimeTypes(['image/*']);
    }

    public function mimeTypes(array $mimeTypes): static
    {
        $this->mimeTypes = $mimeTypes;

        return $this;
    }

    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    public function getMimeTypes(): array
    {
        return $this->mimeTypes;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function getSelectAction(): Action
    {
        return Action::make('select')
            ->label(__('inspirecms::actions.select.label'))
            ->fillForm(fn () => ['records' => $this->getState()])
            ->form(function () {
                $select = MediaBrowser::make('records')
                    ->hiddenLabel()
                    ->multiple($this->isMultiple())
                    ->mimeTypes($this->getMimeTypes());

                return [$select];
            })
            ->modalWidth('7xl')
            ->stickyModalFooter()
            ->stickyModalHeader()
            ->action(function (array $data) {
                $recordKeys = $data['records'] ?? [];
                if (! is_array($recordKeys)) {
                    $recordKeys = [$recordKeys];
                }
                $this->state(array_filter($recordKeys));
            });
    }

    public function getClearAction(): Action
    {
        return Action::make('clear')
            ->label(__('inspirecms::actions.clear.label'))
            ->color('gray')
            ->action(function () {
                $this->state([]);
            });
    }

    public function getFormattedStateForDisplay()
    {
        $state = $this->getState();

        if (is_null($state)) {
            return [];
        }

        $media = static::getMediaAssetModel()::query()->whereKey($state)->get();

        return collect($media)->mapWithKeys(fn ($item) => [
            $item->getKey() => [
                'title' => $item->title,
                'url' => $item->getThumbnail(),
            ],
        ])->all();
    }

    protected function getEloquentQuery()
    {
        return static::getMediaAssetModel()::query()->folders(false);
    }

    protected static function getMediaAssetModel(): string
    {
        return InspireCmsConfig::getMediaAssetModelClass();
    }
}
