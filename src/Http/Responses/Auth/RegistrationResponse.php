<?php

namespace SolutionForest\InspireCms\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Filament\Http\Responses\Auth\RegistrationResponse as FilamentRegistrationResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\InspireCmsConfig;

class RegistrationResponse extends FilamentRegistrationResponse implements RegistrationResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        if (filament()->getCurrentPanel()?->getId() === InspireCmsConfig::getPanelId() && ($importDataUrl = InspireCms::getImportDataUrl())) {  
            return redirect()->intended($importDataUrl);
        }

        return parent::toResponse($request);
    }
}
