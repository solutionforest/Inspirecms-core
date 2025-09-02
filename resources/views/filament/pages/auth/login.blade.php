@use('SolutionForest\InspireCms\InspireCmsConfig')

<x-filament-panels::page.simple>
    @if ((filament()->hasRegistration() && InspireCmsConfig::get('admin.allow_registration', false)) || inspirecms()->needInstall())
        <x-slot name="subheading">
            {{ $this->getSubHeading() }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    {{ $this->content }}

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>