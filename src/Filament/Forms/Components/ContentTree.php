<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Forms\Components\Field;
use SolutionForest\InspireCms\Filament\Forms\Components\Concerns\WithContentTreeNode;

class ContentTree extends Field
{
    use CanLimitItemsLength;
    use WithContentTreeNode;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.content-tree';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (ContentTree $component, $state) {
            $component->state($state ?? []);
        });
    }

    public function getLimits(): array
    {
        return [
            'min' => $this->getMinItems(),
            'max' => $this->getMaxItems(),
        ];
    }
}
