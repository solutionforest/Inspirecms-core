<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class CodeEditor extends Field
{
    protected string $view = 'inspirecms::filament.forms.components.code-editor';
    
    protected int | string | Closure | null $minHeight = 420;
    protected string | Closure | null $customStyle = null;
    protected string | Closure | null $darkModeTheme = null;
    protected string | Closure | null $lightModeTheme = null;
    protected bool | Closure $isReadOnly = false;
    protected bool | Closure $showCopyButton = false;

    public function customStyle(string | null $customStyle): static
    {
        $this->customStyle = $customStyle;

        return $this;
    }

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

    public function isReadOnly(bool | Closure $isReadOnly = false): static
    {
        $this->isReadOnly = $isReadOnly;

        return $this;
    }

    public function showCopyButton(bool | Closure $showCopyButton = true): static
    {
        $this->showCopyButton = $showCopyButton;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsReadOnly(): bool
    {
        return boolval($this->evaluate($this->isReadOnly));
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
     * @return string
     */
    public function getShowCopyButton()
    {
        return $this->evaluate($this->showCopyButton ? "true" : "false");
    }

    /**
     * @return ?string
     */
    public function getCustomStyle()
    {
        return $this->evaluate($this->customStyle);
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
