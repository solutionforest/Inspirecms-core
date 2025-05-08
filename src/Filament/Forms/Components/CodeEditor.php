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

    protected string | Closure | null $darkModeTheme = null;

    protected string | Closure | null $lightModeTheme = null;

    public function minHeight(int | string | Closure | null $minHeight = 768): static
    {
        $this->minHeight = $minHeight;

        return $this;
    }

    public function lightModeTheme(?string $lightModeTheme): static
    {
        $this->lightModeTheme = $lightModeTheme;

        return $this;
    }

    public function darkModeTheme(?string $darkModeTheme): static
    {
        $this->darkModeTheme = $darkModeTheme;

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

    /**
     * @return ?string
     */
    public function getDarkModeTheme()
    {
        return $this->evaluate($this->darkModeTheme);
    }

    /**
     * @return ?string
     */
    public function getLightModeTheme()
    {
        return $this->evaluate($this->lightModeTheme);
    }
}
