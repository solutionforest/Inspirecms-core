<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions;

use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\BelongsToGroup;
use Filament\Actions\Concerns\CanBeHidden;
use Filament\Actions\Concerns\CanBeLabeledFrom;
use Filament\Actions\Concerns\CanBeOutlined;
use Filament\Actions\Concerns\HasDropdown;
use Filament\Actions\Concerns\HasGroupedIcon;
use Filament\Actions\Concerns\HasLabel;
use Filament\Actions\Concerns\HasName;
use Filament\Actions\Concerns\HasSize;
use Filament\Actions\Concerns\HasTooltip;
use Filament\Actions\Contracts\HasLivewire;
use Filament\Actions\StaticAction;
use Filament\Support\Concerns\HasBadge;
use Filament\Support\Concerns\HasColor;
use Filament\Support\Concerns\HasExtraAttributes;
use Filament\Support\Concerns\HasIcon;
use Filament\Support\Facades\FilamentIcon;
use Livewire\Component;

class BaseCreateContentAction extends Action implements HasLivewire
{
    use BelongsToGroup;
    use CanBeHidden {
        isHidden as baseIsHidden;
    }
    use CanBeLabeledFrom;
    use CanBeOutlined;
    use HasBadge;
    use HasColor;
    use HasDropdown;
    use HasExtraAttributes;
    use HasGroupedIcon;
    use HasIcon {
        getIcon as getBaseIcon;
    }
    use HasLabel;
    use HasName;
    use HasSize;
    use HasTooltip;

    // protected ?BaseCreateContentAction $group = null;

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

    public function isBadge(): bool
    {
        return $this->getView() === static::BADGE_VIEW;
    }

    public function button(): static
    {
        return $this->view(static::BUTTON_VIEW);
    }

    public function isButton(): bool
    {
        return $this->getView() === static::BUTTON_VIEW;
    }

    public function grouped(): static
    {
        return $this->view(static::GROUPED_VIEW);
    }

    public function iconButton(): static
    {
        return $this->view(static::ICON_BUTTON_VIEW);
    }

    public function isIconButton(): bool
    {
        return $this->getView() === static::ICON_BUTTON_VIEW;
    }

    public function link(): static
    {
        return $this->view(static::LINK_VIEW);
    }

    public function isLink(): bool
    {
        return $this->getView() === static::LINK_VIEW;
    }

    public function livewire(Component $livewire): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function getLivewire(): object
    {
        if (isset($this->livewire)) {
            return $this->livewire;
        }

        $group = $this->getGroup();

        if (! ($group instanceof HasLivewire)) {
            throw new Exception('This action group does not belong to a Livewire component.');
        }

        return $group->getLivewire();
    }

    public function getLabel(): string
    {
        $label = $this->evaluate($this->label) ?? __('filament-actions::group.trigger.label');

        return $this->shouldTranslateLabel ? __($label) : $label;
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

    /**
     * @return array<string, StaticAction>
     */
    public function getFlatActions(): array
    {
        return $this->flatActions;
    }

    public function getIcon(): string
    {
        return $this->getBaseIcon() ?? FilamentIcon::resolve('actions::action-group') ?? 'heroicon-m-ellipsis-vertical';
    }

    public function isHidden(): bool
    {
        if ($this->baseIsHidden()) {
            return true;
        }

        foreach ($this->getActions() as $action) {
            if ($action->isHiddenInGroup()) {
                continue;
            }

            return false;
        }

        return true;
    }

    // public function group(?BaseCreateContentAction $group): static
    // {
    //     $this->group = $group;

    //     return $this;
    // }

    // public function getGroup(): ?BaseCreateContentAction
    // {
    //     return $this->group;
    // }

    // public function getRootGroup(): ?BaseCreateContentAction
    // {
    //     $group = $this->getGroup();

    //     while ($group) {
    //         $parentGroup = $group->getGroup();

    //         if (! $parentGroup) {
    //             break;
    //         }

    //         $group = $parentGroup;
    //     }

    //     return $group;
    // }
}
