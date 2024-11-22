<?php

namespace SolutionForest\InspireCms\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use SolutionForest\InspireCms\Facades\InspireCms;

class RegistrationResponse implements RegistrationResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->intended(InspireCms::getImportDataUrl() ?? filament()->getUrl());
    }
}
