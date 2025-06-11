<?php

namespace SolutionForest\InspireCms\Http\Middleware;

use Closure;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Http\Request;
use SolutionForest\InspireCms\Licensing\LicenseManager;
use SolutionForest\InspireCms\View\Components\Alert;

class LicenseCheck
{
    public function __construct(
        protected LicenseManager $licenseManager
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $result = $this->licenseManager->verify();

        if (! $result->isSuccess()) {

            // Handle invalid license (redirect to license page, show error, etc.)
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid license: ' . $result->getMessage(),
                ], 403);
            }

            $this->addWarningAlert($result->getReason());
        }

        return $next($request);
    }

    private function addWarningAlert($reason = null)
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            function () use ($reason) {
                $alert = Alert::make(fn () => str_contains($reason, 'expired') ? 'License has expired. Please renew your license.' : __('inspirecms::messages.invalid_license'))
                    ->type('warn')
                    ->size('sm')
                    ->withAttributes([
                        'class' => 'top-alert',
                    ]);

                return $alert->render();
            }
        );
    }
}
