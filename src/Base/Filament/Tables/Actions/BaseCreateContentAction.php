<?php

namespace SolutionForest\InspireCms\Base\Filament\Tables\Actions;

use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\BelongsToGroup;
use Filament\Actions\Concerns\HasDropdown;
use Filament\Actions\Concerns\HasGroupedIcon;
use Filament\Actions\Concerns\HasTooltip;
use Filament\Actions\StaticAction;
use Filament\Tables\Actions\Action;
use Livewire\Component;

class BaseCreateContentAction extends Action
{
    use BelongsToGroup;
    use HasDropdown;
    use HasGroupedIcon;
    use HasTooltip;

    public const BADGE_VIEW = 'filament-actions::badge-group';

    public const BUTTON_VIEW = 'filament-actions::button-group';

    public const GROUPED_VIEW = 'filament-actions::grouped-group';

    public const ICON_BUTTON_VIEW = 'filament-actions::icon-button-group';

    public const LINK_VIEW = 'filament-actions::link-group';

    /**
     * @var array<StaticAction | ActionGroup | BaseCreateContentAction>
     */
    protected array $actions;

    /**
     * @var array<string, StaticAction>
     */
    protected array $flatActions;

    protected Component $livewire;

    protected string $evaluationIdentifier = 'group';

    protected string $viewIdentifier = 'group';

    protected function setUp(): void
    {
        parent::setUp();

        $this->button();

        $this->name($this->getDefaultName());

        $this->icon('heroicon-m-chevron-down');
    }

    /**
     * @param  array<StaticAction | ActionGroup>  $actions
     */
    public function actions(array $actions): static
    {
        $this->actions = [];
        $this->flatActions = [];

        foreach ($actions as $action) {

            if ($action instanceof ActionGroup) {
                $action->dropdownPlacement('right-top');

                $this->flatActions = [
                    ...$this->flatActions,
                    ...$action->getFlatActions(),
                ];
            } else {
                $this->flatActions[$action->getName()] = $action;
            }

            $this->actions[] = $action;
        }

        return $this;
    }

    /**
     * @return array<StaticAction | ActionGroup>
     */
    public function getActions(): array
    {
        return array_map(
            fn (StaticAction | ActionGroup $action) => $action->defaultView($action::GROUPED_VIEW),
            $this->actions,
        );
    }
}
