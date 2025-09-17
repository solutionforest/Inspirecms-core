<?php

namespace SolutionForest\InspireCms\Base\Filament\Pages\Concerns;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

trait WithBackgroundImageLayout
{
    use HaveBackgroundImage;

    public function bootWithBackgroundImageLayout()
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::SIMPLE_LAYOUT_START,
            function () {
                // Add theme switcher button at top-left corner
                return Blade::render(<<<'BLADE'
                    <div class="theme-switcher-ctn">
                        <x-filament-panels::theme-switcher />
                    </div>
                BLADE);
            },
            [static::class],
        );
    }

    public function getExtraBodyAttributes(): array
    {
        $attributes = parent::getExtraBodyAttributes();

        $appendToAttributes = function ($key, $value) use (&$attributes) {
            if (! isset($attributes[$key])) {
                $attributes[$key] = '';
            }
            if (is_array($attributes[$key])) {
                $attributes[$key][] = $value;
            } else {
                $attributes[$key] .= ' ' . $value;
            }
        };

        if (($bgImage = $this->getBackgroundImage()) && filled($bgImage)) {
            $appendToAttributes('style', "--panel-background-image: url($bgImage);");
        }

        $appendToAttributes('class', 'dynamic-bg-body');

        return $attributes;
    }
}
