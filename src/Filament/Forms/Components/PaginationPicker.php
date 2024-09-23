<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Support\Concerns\HasReorderAnimationDuration;

class PaginationPicker extends Field
{
    use Concerns\HasPaginationOptions;
    use Concerns\WithTable;
    use HasPlaceholder;
    use HasExtraAlpineAttributes;
    use HasReorderAnimationDuration;
    use HasExtraInputAttributes;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.pagination-picker';

    protected string | Closure | null $separator = null;

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
            'select' => $this->getSelectAction(),
        ]);
    }

    public function separator(string | Closure | null $separator = ','): static
    {
        $this->separator = $separator;

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

    public function getSelectAction(): Action
    {
        return Action::make('select')
            ->label(__('inspirecms::actions.select.label'))
            ->fillForm(fn () => ['records' => $this->getState()])
            ->form(function () {
                $select = PaginationCheckboxList::make('records')
                    ->hiddenLabel()
                    ->paginationOptions($this->paginationOptions)
                    ->perPage($this->perPage)
                    ->contentGrid($this->contentGrid)
                    ->tableColumns($this->tableColumns);

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
}
