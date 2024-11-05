<?php

namespace SolutionForest\InspireCms\Filament\Widgets\AlertOverview;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component implements Htmlable
{
    protected string | Htmlable | Closure $label = '';

    protected string $type = 'info';

    /**
     * @param  scalar | Htmlable | Closure  $value
     */
    final public function __construct(string | Htmlable | Closure $label, $type)
    {
        $this->label($label);
        $this->type($type);
    }

    /**
     * @param  scalar | Htmlable | Closure  $value
     */
    public static function make(string | Htmlable | Closure $label, string $type): static
    {
        return app(static::class, ['label' => $label, 'type' => $type]);
    }

    public function label(string | Htmlable | Closure $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string | Htmlable
    {
        return is_callable($this->label) ? ($this->label)() : $this->label;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getColor(): string
    {
        return match ($this->type) {
            'success' => 'success',
            'error', 'danger' => 'danger',
            'warning', 'warn' => 'warning',
            'info' => 'info',
            'primary' => 'primary',
            'secondary' => 'secondary',
            default => 'gray',
        };
    }

    public function render(): View
    {
        return view('inspirecms::filament.widgets.alert-overview.alert', $this->data());
    }

    public function toHtml(): string
    {
        return $this->render()->render();
    }
}
