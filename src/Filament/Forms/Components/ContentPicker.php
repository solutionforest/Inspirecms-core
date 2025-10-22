<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Support\Enums\Size;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Filament\Forms\Components\Concerns\WithContentTreeNode;

use function Filament\Forms\array_move_after;
use function Filament\Forms\array_move_before;

class ContentPicker extends Field
{
    use CanLimitItemsLength;
    use HasPlaceholder;
    use WithContentTreeNode;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.content-picker';

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
            fn (self $component): Action => $component->getSelectAction(),
            fn (self $component): Action => $component->getClearAction(),
            fn (self $component): Action => $component->getMoveUpAction(),
            fn (self $component): Action => $component->getMoveDownAction(),
            fn (self $component): Action => $component->getDeleteAction(),
        ]);
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

    public function getSelectAction(): Action
    {
        return Action::make('select')
            ->label(__('inspirecms::buttons.select.label'))
            ->modalWidth('5xl')
            ->slideOver()
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalSubmitActionLabel(__('inspirecms::buttons.choose.label'))
            ->fillForm(['selection' => $this->getState()])
            ->schema(function () {
                $selector = ContentTree::make('selection')
                    ->hiddenLabel()
                    // todo: add translations
                    ->validationAttribute('selection')
                    ->filter($this->getFilter()->toArray())
                    ->filteringByPermission($this->isFilteringByPermission());

                if ($this->minItems != null) {
                    $selector->minItems($this->minItems);
                }

                if ($this->maxItems != null) {
                    $selector->maxItems($this->maxItems);
                }

                if ($this->startNode != null) {
                    $selector->startNode($this->startNode);
                }

                if ($this->modifySelectActionSelectorUsing) {
                    $selector = $this->evaluate($this->modifySelectActionSelectorUsing, [
                        'selector' => $selector,
                    ]) ?? $selector;
                }

                return [$selector];
            })
            ->action(function (array $data, $action) {

                $ids = $data['selection'] ?? [];

                if (! is_array($ids)) {
                    $ids = is_null($ids) || empty($ids) ? [] : [$ids];
                }
                // Filter out any null or empty values
                $ids = array_filter($ids);
                // Filter out any duplicate values
                $ids = array_values(array_unique($ids));

                // Filter out if exceed limits
                if (($max = $this->getMaxItems()) &&
                    count($ids) > $max
                ) {
                    $ids = array_slice($ids, 0, $max);
                }

                return $this->state($ids)->callAfterStateUpdated();
            });
    }

    public function getClearAction(): Action
    {
        return Action::make('clear')
            ->label(__('inspirecms::buttons.clear.label'))
            ->color('gray')
            ->action(function () {
                $this->state([]);
            });
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
