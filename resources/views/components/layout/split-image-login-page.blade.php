@props([
    'after' => null,
    'heading' => null,
    'subheading' => null,
    'image' => null,
])
@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">

    <div 
        class="fi-simple-layout split-image-layout flex min-h-screen flex-col items-center bg-primary-200 dark:bg-primary-800/80" 
        @style([
            "--panel-background-image: url($image)" => isset($image) && filled($image),
        ])
    >
        <div class="absolute end-0 top-0 flex h-16 items-center gap-x-4 pe-4 md:pe-6 lg:pe-8">
            <div class="bg-white rounded-lg dark:bg-gray-900">
                <x-filament-panels::theme-switcher />
            </div>
        </div>

        <div
            class="split-image-main-ctn flex w-full flex-grow items-center justify-center sm:justify-end sm:items-stretch"
        >
            <main
                @class([
                    'split-image-main w-full bg-white px-6 py-12 shadow-lg ring-1 ring-gray-950/5 lg:bg-white/95 lg:dark:bg-gray-900/80 dark:bg-gray-900 dark:ring-white/10 sm:px-12',
                    match ($maxWidth ?? null) {
                        MaxWidth::ExtraSmall, 'xs' => 'sm:max-w-xs',
                        MaxWidth::Small, 'sm' => 'sm:max-w-sm',
                        MaxWidth::Medium, 'md' => 'sm:max-w-md',
                        MaxWidth::ExtraLarge, 'xl' => 'sm:max-w-xl',
                        MaxWidth::TwoExtraLarge, '2xl' => 'sm:max-w-2xl',
                        MaxWidth::ThreeExtraLarge, '3xl' => 'sm:max-w-3xl',
                        MaxWidth::FourExtraLarge, '4xl' => 'sm:max-w-4xl',
                        MaxWidth::FiveExtraLarge, '5xl' => 'sm:max-w-5xl',
                        MaxWidth::SixExtraLarge, '6xl' => 'sm:max-w-6xl',
                        MaxWidth::SevenExtraLarge, '7xl' => 'sm:max-w-7xl',
                        default => 'sm:max-w-lg',
                    },
                ])
            >
                {{ $slot }}
            </main>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $livewire->getRenderHookScopes()) }}
    </div>
</x-filament-panels::layout.base>