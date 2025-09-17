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
        // FilamentView::registerRenderHook(
        //     PanelsRenderHook::SIMPLE_LAYOUT_START,
        //     function () {
        //         // Add theme switcher button at top-left corner
        //         return str(<<<'HTML'
        //             <div class="hidden lg:flex lg:items-center lg:justify-center lg:p-12 lg:bg-black/20">
        //                 <div class="max-w-lg text-center lg:text-left">
        //                     <h1 class="text-4xl xl:text-5xl font-bold text-white mb-6 leading-tight">
        //                         Welcome to Our Platform
        //                     </h1>
        //                     <p class="text-xl text-white/80 mb-8 leading-relaxed">
        //                         Join thousands of users who trust our secure authentication system. Experience the future of login.
        //                     </p>
        //                     <div class="space-y-4">
        //                         <div class="flex items-center text-white/70">
        //                             <svg class="w-6 h-6 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        //                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        //                             </svg>
        //                             Secure & Encrypted
        //                         </div>
        //                         <div class="flex items-center text-white/70">
        //                             <svg class="w-6 h-6 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        //                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        //                             </svg>
        //                             Fast & Reliable
        //                         </div>
        //                         <div class="flex items-center text-white/70">
        //                             <svg class="w-6 h-6 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        //                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        //                             </svg>
        //                             24/7 Support
        //                         </div>
        //                     </div>
        //                 </div>
        //             </div>
        //         HTML)->toHtmlString();
        //     },
        //     [static::class],
        // );
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
