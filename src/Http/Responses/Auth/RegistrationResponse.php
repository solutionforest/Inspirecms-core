<?php

namespace SolutionForest\InspireCms\Http\Responses\Auth;

use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\InspireCmsConfig;

class RegistrationResponse extends \Filament\Auth\Http\Responses\RegistrationResponse implements \Filament\Auth\Http\Responses\Contracts\RegistrationResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        if (filament()->getCurrentOrDefaultPanel()?->getId() === InspireCmsConfig::getPanelId() && ($importDataUrl = InspireCms::getImportDataUrl())) {
            return redirect()->intended($importDataUrl);
        }

        return parent::toResponse($request);
    }
}
