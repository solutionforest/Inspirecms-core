<?php

namespace SolutionForest\InspireCms\ImportData;

use Illuminate\Support\Facades\File;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class JsonFileReader
{
    public function readFromPath(string $filePath): array
    {
        if (! File::exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $content = File::get($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in file: {$filePath}");
        }

        return $data;
    }

    public function readFromFile(TemporaryUploadedFile $file): array
    {
        //check if the file is a json file
        if ($file->getMimeType() !== 'application/json') {
            throw new \Exception('Invalid file type. Only JSON files are allowed');
        }

        $data = json_decode($file->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in file');
        }

        return $data;
    }
}
