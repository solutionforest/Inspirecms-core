<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\Services\ImportServiceInterface;

class ImportController extends Controller
{
    protected ImportServiceInterface $importService;

    public function __construct(ImportServiceInterface $importService)
    {
        $this->importService = $importService;
    }

    public function sample()
    {
        $file = $this->importService->buildSampleZip();

        return response()
            ->download($file, 'import-sample-'.uniqid().'.zip')
            ->deleteFileAfterSend(true);
    }
}
