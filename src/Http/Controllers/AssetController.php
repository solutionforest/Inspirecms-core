<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\Services\AssetService;

class AssetController extends Controller
{
    protected AssetService $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function __invoke($key)
    {
        $asset = $this->assetService->findByKey($key);

        $media = $asset?->getFirstMedia();

        if (is_null($media)) {
            abort(404);
        }

        return $media->toInlineResponse(request());
    }
}
