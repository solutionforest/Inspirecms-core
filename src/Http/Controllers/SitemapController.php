<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\Factories\SitemapGeneratorFactory;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $filePath = SitemapGeneratorFactory::create()->getFilePath();

        if (! file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/xml',
        ]);

    }
}
