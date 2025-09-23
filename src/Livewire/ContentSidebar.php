<?php

namespace SolutionForest\InspireCms\Livewire;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\SelectAction;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Dtos\LanguageDto;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\AdjustChildOrderAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\CreateContentAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\MoveContentAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\SetDefaultContentPageAction;
use SolutionForest\InspireCms\Filament\Resources\Contents\Actions\UpdateContentRouteAction;

class ContentSidebar extends BaseContentTreeNode
{
    protected static bool $showNodeActions = true;

    protected static bool $enableNodeUrls = true;

    public ?int $maxSelections = 1; // Maximum number of selections allowed (null = unlimited)
    
    public ?string $filamentPage = null;

    protected $listeners = [
        'updatedActiveLocale' => '$refresh',
    ];

    protected function queryString()
    {
        return [
            'activeLocale' => ['except' => null],
        ];
    }

    protected function getToolbarActions(): array
    {
        return [
            SelectAction::make('activeLocale')
                ->options(
                    collect(InspireCms::getAllAvailableLanguages())
                        ->map(fn (LanguageDto $lang) => $lang->getLabel())
                        ->all()
                ),
        ];
    }

    protected function getNavigationHeaderActions(): array
    {
        return [

            ActionGroup::make([

                CreateContentAction::make()
                    ->color('primary'),

                AdjustChildOrderAction::make()
                    ->after(fn () => $this->refreshTree()),

            ])->dropdownPlacement('right-start'),

        ];
    }

    protected function getNodeItemActions(): array
    {
        return [

            ActionGroup::make([

                CreateContentAction::make()
                    ->color('primary')
                    ->name('createContentFromNode')
                    ->parentContentKey(fn (array $arguments) => $arguments['node']['id'] ?? null)
                    ->parentDocumentType(fn (array $arguments) => $arguments['node']['__content_document_type_id'] ?? null)
                    ->nodeTitleUsing(fn (array $arguments) => $arguments['node']['name'] ?? null),

                AdjustChildOrderAction::make()
                    ->name('reorderContentChildrenFromNode')
                    ->nodeParentId(fn (array $arguments) => $arguments['node']['__content_tree_id'] ?? null)
                    ->after(fn () => $this->refreshTree()),

                ActionGroup::make([

                    SetDefaultContentPageAction::make()
                        ->after(fn () => $this->refreshTree()),

                    UpdateContentRouteAction::make()
                        ->after(fn () => $this->refreshTree()),

                    ActionGroup::make([

                        MoveContentAction::make()
                            ->name('moveContentToUnderRoot')
                            ->moveUnderRoot(true)
                            ->after(fn () => $this->refreshTree()),

                        MoveContentAction::make()
                            ->moveUnderRoot(false)
                            ->after(fn () => $this->refreshTree()),

                    ])
                        ->color('gray')
                        ->iconPosition(IconPosition::After)
                        ->icon(Heroicon::ArrowRight)
                        ->label(__('inspirecms::buttons.move_to.label'))
                        ->dropdownPlacement('right-start'),

                    DeleteAction::make()
                        ->after(fn () => $this->refreshTree()),

                ])->dropdown(false),

            ])->dropdownPlacement('right-start'),
        ];
    }

    protected function getElquentQuery(): Builder
    {
        return parent::getElquentQuery()
            ->with([
                'documentType', // icon and pre-check for UpdateContentRouteAction

                'nestableTree', // use for sorting action
                'locked', // pre-check for Record action, UpdateContentRouteAction
            ])
            // sort by tree id for display consistency
            ->sortedByTree();
    }

    public function render()
    {
        return view('inspirecms::livewire.content-sidebar', $this->viewData());
    }
}
