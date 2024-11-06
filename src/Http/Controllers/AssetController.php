<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;

class AssetController extends Controller
{
    public function __invoke($key)
    {
        $asset = inspirecms_asset()->findByKey($key);

        $media = $asset?->getFirstMedia();

        if (is_null($media)) {
            abort(404);
        }

        return $media->toInlineResponse(request());
    }
}
