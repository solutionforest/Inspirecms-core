<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Field;

class CodeEditor extends Field
{
    use HasExtraInputAttributes;

    protected string $view = 'inspirecms::filament.forms.components.code-editor';

    protected int | string | Closure | null $minHeight = 420;

    public function minHeight(int | string | Closure | null $minHeight = 768): static
    {
        $this->minHeight = $minHeight;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getMinHeight()
    {
        $height = $this->evaluate($this->minHeight);

        if (is_numeric($height)) {
            return (int) $height . 'px';
        }

        if (is_string($height)) {
            return $height;
        }

        return null;
    }
}
