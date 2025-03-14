<?php

namespace SolutionForest\InspireCms\Http\Middleware;

use SolutionForest\InspireCms\Licensing\LicenseManager;
use Closure;
use Illuminate\Http\Request;

class LicenseCheck
{
    public function __construct(
        protected LicenseManager $licenseManager
    )
    {
    }
    
    public function handle(Request $request, Closure $next)
    {
        // Verify license
        $result = $this->licenseManager->coreValid();

        dd($result);
        
        if (!$result['valid']) {
            // Handle invalid license (redirect to license page, show error, etc.)
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid license: ' . $result['message']
                ], 403);
            }
            
            return redirect()->route('license.error')
                ->with('license_error', $result['message']);
        }
        
        return $next($request);
    }
}
