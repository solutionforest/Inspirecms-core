<?php

namespace SolutionForest\InspireCms\Filament\Resources\NavigationResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Schema;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource;
use SolutionForest\InspireCms\Filament\Resources\NavigationResource\Concerns\NavigationListPageTrait;
use SolutionForest\InspireCms\InspireCmsConfig;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;

class ListNavigationTree extends BaseListRecords
{
    use NavigationListPageTrait;
    use Translatable;

    public function getActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('navigation', NavigationResource::class);
    }

    #[On('refreshAllTree')]
    public function getAllCategories(): array
    {
        return $this->getModel()::query()->groupBy('category')->pluck('category')->toArray();
    }

    public function updatingActiveLocale($value)
    {
        $this->dispatch('refreshAllTree');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components(fn () => collect($this->getAllCategories())
                ->map(function ($category) {

                    $livewireData = [
                        'category' => $category,
                        ...$this->getNavigationTreeData($category) 
                    ];

                    $livewireId = "navigation-tree-{$category}";

                    return Section::make(ucfirst($category))
                        ->schema([
                            Livewire::make('inspirecms::navigation-tree', $livewireData)
                                ->id($livewireId)
                        ]);
                })
                ->all()
            );
    }

    public function getNavigationTreeData($category): array
    {
        return [
            'activeLocale' => $this->getActiveActionsLocale() ?? null,
            'maxDepth' => -1,
            'maxVisibleDepth' => 20,
        ];
    }
}
