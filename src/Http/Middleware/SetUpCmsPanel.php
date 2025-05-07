<?php

namespace SolutionForest\InspireCms\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use SolutionForest\InspireCms\InspireCmsConfig;

class SetUpCmsPanel
{
    public function handle(Request $request, Closure $next)
    {
        $panelId = InspireCmsConfig::getPanelId();

        $panel = Filament::getPanel($panelId);

        Filament::setCurrentPanel($panel);

        Filament::bootCurrentPanel();

        return $next($request);
    }
}
