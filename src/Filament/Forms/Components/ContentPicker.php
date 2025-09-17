<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
use Filament\Support\Enums\Size;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Filament\Forms\Components\Concerns\InteractsWithContentTreeModal;

use function Filament\Forms\array_move_after;
use function Filament\Forms\array_move_before;

class ContentPicker extends Field
{
    use HasPlaceholder;
    use InteractsWithContentTreeModal;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.content-picker.index';

    protected ?Closure $recordTitleUsing = null;

    protected bool $isReorderable = true;

    protected bool $isDeletable = true;

    protected ?Closure $modifySelectActionSelectorUsing = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (ContentPicker $component, $state) {
            if (! is_array($state)) {
                $state = [$state];
            }
            $state = array_filter($state);
            $component->state($state);
        });

        $this->registerActions([
            $this->getMoveUpAction(),
            $this->getMoveDownAction(),
            $this->getDeleteAction(),
        ]);
    }

    #[ExposedLivewireMethod]
    public function clearSelected()
    {
        $this->rawState([]);
    }

    #[ExposedLivewireMethod]
    public function updateSelected($ids)
    {
        ray($ids);
        $this->rawState($ids);
    }

    public function recordTitleUsing(Closure $callback): static
    {
        $this->recordTitleUsing = $callback;

        return $this;
    }

    public function reorderable(bool $condition = true): static
    {
        $this->isReorderable = $condition;

        return $this;
    }

    public function deletable(bool $condition = true): static
    {
        $this->isDeletable = $condition;

        return $this;
    }

    public function getFormattedStateForDisplay($state = null)
    {
        $state ??= $this->getState();

        if (! $state) {
            return [];
        }

        $records = $this->getEloquentQuery()?->whereKey($state)->get();

        $formattedState = $records
            ->keyBy(fn (Model $record) => $record->getKey())
            ->map(function (Model $record) {
                $title = $this->getRecordTitle($record) ?? ($record->hasAttribute('title') ? $record->title : $record->getKey());

                if ($record->hasAttribute('deleted_at') && $record->deleted_at) {
                    $title .= ' (' . __('inspirecms::messages.deleted') . ')';
                }

                return $title;
            })
            ->toArray() ?? [];

        $orderedState = [];
        foreach ($state as $key) {
            if (array_key_exists($key, $formattedState)) {
                $orderedState[$key] = $formattedState[$key];
            }
        }

        return $orderedState;
    }

    public function isMultiple(): bool
    {
        return $this->getMaxItems() !== 1;
    }

    public function modifySelectActionSelectorUsing(Closure $callback): static
    {
        $this->modifySelectActionSelectorUsing = $callback;

        return $this;
    }

    public function getRecordTitle(Model $record): ?string
    {
        return $this->evaluate($this->recordTitleUsing, [
            'record' => $record,
        ]);
    }

    public function isReorderable(): bool
    {
        return boolval($this->evaluate($this->isReorderable));
    }

    public function isDeletable(): bool
    {
        return boolval($this->evaluate($this->isDeletable));
    }

    public function getMoveUpAction(): Action
    {
        return Action::make('moveUp')
            ->label(__('filament-forms::components.repeater.actions.move_up.label'))
            ->icon(FilamentIcon::resolve('inspirecms::move_up'))
            ->color('gray')
            ->action(function (array $arguments, ContentPicker $component): void {

                $formattedState = Arr::mapWithKeys($component->getState(), fn ($key) => [$key => $key]);

                $items = array_move_before($formattedState, $arguments['item']);

                $component->state(array_values($items));

                $component->callAfterStateUpdated();
            })
            ->iconButton()
            ->size(Size::Small)
            ->disabled(fn (array $arguments) => $arguments['disabled'] === true)
            ->visible(fn (ContentPicker $component): bool => $component->isReorderable());
    }

    public function getMoveDownAction(): Action
    {
        return Action::make('moveDown')
            ->label(__('filament-forms::components.repeater.actions.move_down.label'))
            ->icon(FilamentIcon::resolve('inspirecms::move_down'))
            ->color('gray')
            ->action(function (array $arguments, ContentPicker $component): void {

                $formattedState = Arr::mapWithKeys($component->getState(), fn ($key) => [$key => $key]);
                $items = array_move_after($formattedState, $arguments['item']);

                $component->state(array_values($items));

                $component->callAfterStateUpdated();
            })
            ->iconButton()
            ->size(Size::Small)
            ->disabled(fn (array $arguments) => $arguments['disabled'] === true)
            ->visible(fn (ContentPicker $component): bool => $component->isReorderable());
    }

    public function getDeleteAction(): Action
    {
        return Action::make('delete')
            ->label(__('filament-forms::components.repeater.actions.delete.label'))
            ->icon(FilamentIcon::resolve('inspirecms::delete'))
            ->color('danger')
            ->action(function (array $arguments, ContentPicker $component): void {
                $items = $component->getState();

                $items = Arr::where($items, fn ($key) => $key != $arguments['item']);

                $component->state(array_values($items));

                $component->callAfterStateUpdated();
            })
            ->iconButton()
            ->size(Size::Small)
            ->visible(fn (ContentPicker $component): bool => $component->isDeletable());
    }
}
