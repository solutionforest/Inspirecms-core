<?php

namespace SolutionForest\InspireCms\View\Components;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component implements Htmlable
{
    use EvaluatesClosures;

    protected string | Htmlable | Closure $message = '';

    protected string $type;

    protected string $size;

    /**
     * @param  scalar | Htmlable | Closure  $message
     */
    final public function __construct(string | Htmlable | Closure $message, string $type = 'info', string $size = 'lg')
    {
        $this->message($message);
        $this->type($type);
        $this->size($size);
    }

    /**
     * @param  scalar | Htmlable | Closure  $value
     */
    public static function make(string | Htmlable | Closure $message, string $type = 'info', string $size = 'lg'): static
    {
        return app(static::class, ['message' => $message, 'type' => $type, 'size' => $size]);
    }

    public function message(string | Htmlable | Closure $label): static
    {
        $this->message = $label;

        return $this;
    }

    public function getMessage(): string | Htmlable
    {
        return $this->evaluate($this->message) ?? '';
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function size(string $size): static
    {
        $this->size = $size;

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

    public function getIcon(): string
    {
        return match ($this->type) {
            'success' => FilamentIcon::resolve('inspirecms::success') ?? 'heroicon-o-check-circle',
            'error', 'danger' => FilamentIcon::resolve('inspirecms::error') ?? 'heroicon-o-exclamation-circle',
            'warning', 'warn' => FilamentIcon::resolve('inspirecms::warn') ?? 'heroicon-o-exclamation-triangle',
            default =>  FilamentIcon::resolve('inspirecms::info') ?? 'heroicon-o-information-circle',
        };
    }

    public function getSize(): string
    {
        return match ($this->size) {
            'sm' => 'sm',
            'md' => 'md',
            'lg' => 'lg',
            default => 'md',
        };
    }

    public function render(): View
    {
        $viewData = $this->data();

        // unset the methods from this class
        foreach (get_class_methods($this) as $method) {
            unset($viewData[$method]);
        }

        $viewData['color'] = $this->getColor();
        $viewData['icon'] = $this->getIcon();
        $viewData['size'] = $this->getSize();
        $viewData['message'] = $this->getMessage();

        return view('inspirecms::components.alert.index', $viewData);
    }

    public function toHtml(): string
    {
        return $this->render()->render();
    }
}
