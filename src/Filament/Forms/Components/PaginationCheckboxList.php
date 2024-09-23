<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class PaginationCheckboxList extends Field
{
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
    }
}
