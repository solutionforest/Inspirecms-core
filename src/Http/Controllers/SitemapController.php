<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\InspireCmsConfig;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $filePath = InspireCmsConfig::get('content.sitemap.file_path');

        if (! file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/xml',
        ]);

    }
}
