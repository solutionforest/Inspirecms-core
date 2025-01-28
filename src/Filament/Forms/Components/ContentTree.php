<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\Concerns\CanLimitItemsLength;
use Filament\Forms\Components\Field;
use SolutionForest\InspireCms\Filament\Forms\Components\Concerns\HasContentTreeFilter;

class ContentTree extends Field
{
    use CanLimitItemsLength;
    use HasContentTreeFilter;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.content-tree';

    public ?string $startNode = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (ContentTree $component, $state) {
            $component->state($state ?? []);
        });

        $this->dehydrateStateUsing(function ($state) {
            return array_filter($state, fn ($value) => $value !== null);
        });
    }

    public function config(array $config): static
    {
        return $this->state($config);
    }
    
    public function startNode(string $parentId): static
    {
        $this->startNode = $parentId;

        return $this;
    }

    public function getStartNode(): ?string
    {
        return $this->startNode;
    }
    
    public function getLimits(): array
    {
        return [
            'min' => $this->getMinItems(),
            'max' => $this->getMaxItems(),
        ];
    }
}
