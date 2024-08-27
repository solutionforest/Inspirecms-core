<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Component;

class RevertOrderGroup extends Component
{
    protected null|Closure|string $revertBreakPoint = null;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.revert-order-group';

    /**
     * @param  array<Component> | Closure  $schema
     */
    final public function __construct(array | Closure $schema = [])
    {
        $this->schema($schema);
    }

    /**
     * @param  array<Component> | Closure  $schema
     */
    public static function make(array | Closure $schema = []): static
    {
        $static = app(static::class, ['schema' => $schema]);
        $static->configure();

        return $static;
    }

    public function revertBreakPoint(Closure|string $breakPoint): static
    {
        $this->revertBreakPoint = $breakPoint;

        return $this;
    }

    public function getRevertBreakPoint(): ?string
    {
        return $this->evaluate($this->revertBreakPoint);
    }
}
