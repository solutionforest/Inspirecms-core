<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Support\Concerns\HasReorderAnimationDuration;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

use function Filament\Forms\array_move_after;
use function Filament\Forms\array_move_before;

class PaginationPicker extends Field
{
    use CanLimitItemsLength;
    use Concerns\HasPaginationOptions;
    use Concerns\WithTable;
    use HasExtraAlpineAttributes;
    use HasExtraInputAttributes;
    use HasPlaceholder;
    use HasReorderAnimationDuration;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.pagination-picker';

    protected string | Closure | null $separator = null;

    protected ?Closure $recordTitleUsing = null;

    protected bool $isReorderable = true;

    protected bool $isDeletable = true;

    protected ?Closure $modifySelectActionSelectorUsing = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(static function (PaginationPicker $component, $state) {
            if (is_array($state)) {
                return;
            }

            if (! ($separator = $component->getSeparator())) {
                $component->state([]);

                return;
            }

            $state = explode($separator, $state ?? '');

            if (count($state) === 1 && blank($state[0])) {
                $state = [];
            }

            $component->state($state);
        });

        $this->dehydrateStateUsing(static function (PaginationPicker $component, $state) {
            if ($separator = $component->getSeparator()) {
                return implode($separator, $state);
            }

            return $state;
        });

        $this->registerActions([
            $this->getSelectAction(),
            $this->getClearAction(),
            $this->getMoveUpAction(),
            $this->getMoveDownAction(),
            $this->getDeleteAction(),
        ]);
    }

    public function separator(string | Closure | null $separator = ','): static
    {
        $this->separator = $separator;

        return $this;
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

    public function modifySelectActionSelectorUsing(Closure $callback): static
    {
        $this->modifySelectActionSelectorUsing = $callback;

        return $this;
    }

    public function getSeparator(): ?string
    {
        return $this->evaluate($this->separator);
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
            ->label(__('inspirecms::actions.select.label'))
            ->fillForm(fn () => ['records' => $this->getState()])
            ->modalWidth('7xl')
            ->stickyModalHeader()->stickyModalFooter()
            ->form(function () {
                $select = PaginationCheckboxList::make('records')
                    ->hiddenLabel()
                    ->paginationOptions(fn () => $this->getPaginationOptionsQuery())
                    ->perPage($this->perPage)
                    ->contentGrid($this->contentGrid)
                    ->tableColumns($this->tableColumns);

                if ($this->minItems) {
                    $select->minItems($this->minItems);
                }

                if ($this->maxItems) {
                    $select->maxItems($this->maxItems);
                }

                if ($this->modifySelectActionSelectorUsing) {
                    $select = $this->evaluate($this->modifySelectActionSelectorUsing, [
                        'select' => $select,
                    ]) ?? $select;
                }

                return [$select];
            })
            ->action(function (array $data) {
                $recordKeys = array_filter($data['records'] ?? []);
                $this->state($recordKeys);
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

    public function getMoveUpAction(): Action
    {
        return Action::make('moveUp')
            ->label(__('filament-forms::components.repeater.actions.move_up.label'))
            ->icon(FilamentIcon::resolve('forms::components.repeater.actions.move-up') ?? 'heroicon-m-arrow-up')
            ->color('gray')
            ->action(function (array $arguments, PaginationPicker $component): void {

                $formattedState = Arr::mapWithKeys($component->getState(), fn ($key) => [$key => $key]);

                $items = array_move_before($formattedState, $arguments['item']);

                $component->state(array_values($items));

                $component->callAfterStateUpdated();
            })
            ->iconButton()
            ->size(ActionSize::Small)
            ->disabled(fn (array $arguments) => $arguments['disabled'] === true)
            ->visible(fn (PaginationPicker $component): bool => $component->isReorderable());
    }

    public function getMoveDownAction(): Action
    {
        return Action::make('moveDown')
            ->label(__('filament-forms::components.repeater.actions.move_down.label'))
            ->icon(FilamentIcon::resolve('forms::components.repeater.actions.move-down') ?? 'heroicon-m-arrow-down')
            ->color('gray')
            ->action(function (array $arguments, PaginationPicker $component): void {

                $formattedState = Arr::mapWithKeys($component->getState(), fn ($key) => [$key => $key]);
                $items = array_move_after($formattedState, $arguments['item']);

                $component->state(array_values($items));

                $component->callAfterStateUpdated();
            })
            ->iconButton()
            ->size(ActionSize::Small)
            ->disabled(fn (array $arguments) => $arguments['disabled'] === true)
            ->visible(fn (PaginationPicker $component): bool => $component->isReorderable());
    }

    public function getDeleteAction(): Action
    {
        return Action::make('delete')
            ->label(__('filament-forms::components.repeater.actions.delete.label'))
            ->icon(FilamentIcon::resolve('forms::components.repeater.actions.delete') ?? 'heroicon-m-trash')
            ->color('danger')
            ->action(function (array $arguments, PaginationPicker $component): void {
                $items = $component->getState();

                $items = Arr::where($items, fn ($key) => $key != $arguments['item']);

                $component->state(array_values($items));

                $component->callAfterStateUpdated();
            })
            ->iconButton()
            ->size(ActionSize::Small)
            ->visible(fn (PaginationPicker $component): bool => $component->isDeletable());
    }

    public function getFormattedStateForDisplay($state = null)
    {
        $state ??= $this->getState();

        if (! $state) {
            return [];
        }

        $records = $this->getPaginationOptionsQuery()?->whereKey($state)->get();

        $formattedState = $records
            ->mapWithKeys(fn ($record) => [
                $record->getKey() => $this->getRecordTitle($record) ?? $record->getKey(),
            ])
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
}
