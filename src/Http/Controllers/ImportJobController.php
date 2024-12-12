<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\Services\ImportJobServiceInterface;

class ImportJobController extends Controller
{
    protected ImportJobServiceInterface $importJobService;

    public function __construct(ImportJobServiceInterface $importJobService)
    {
        $this->importJobService = $importJobService;
    }

    public function sample()
    {
        $file = $this->importJobService->buildSampleZip();

        return response()
            ->download($file, 'import-job-sample-'.uniqid().'.zip')
            ->deleteFileAfterSend(true);
    }
}
