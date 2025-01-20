<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Forms\Components\Field;

class PaginationCheckboxList extends Field
{
    use CanLimitItemsLength;
    use Concerns\HasPaginationOptions;
    use Concerns\WithTable;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.pagination-checkbox-list';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(static function (PaginationCheckboxList $component, $state) {
            if (is_array($state)) {
                return;
            }

            $component->state([]);
        });

        $this->registerActions([
            'gotoPage' => Action::make('gotoPage')
                ->action(function (PaginationCheckboxList $component, $arguments) {
                    $page = $arguments['page'] ?? null;
                    if (is_int($page) && $page > 0) {
                        $component->currentPage = $page;
                    }
                }),
        ]);
    }

    public function isMultiple(): bool
    {
        return $this->getMaxItems() !== 1;
    }
}
