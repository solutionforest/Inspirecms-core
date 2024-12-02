<?php

namespace SolutionForest\InspireCms\Helpers;

class FileHelper
{
    public static function buildZipFromFolder(string $pathToZip, string $zipFullResultPath)
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipFullResultPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            
            /** @var \SplFileInfo[] $files */
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pathToZip));

            foreach ($files as $name => $file) {
                $filePath = $file->getPathName();
                $relativePath = substr($filePath, strlen($pathToZip) + 1);

                if (!$file->isDir()) {
                    $zip->addFile($filePath, $relativePath);
                } else {
                    if ($relativePath !== false) {
                        $zip->addEmptyDir($relativePath);
                    }
                }
            }

            $zip->close();
        }
        else {
            throw new \Exception('Cannot open zip file at ' . $zipFullResultPath);
        }
    }

    public static function unzipFile(string $zipFilePath, string $extractTo)
    {
        $zip = new \ZipArchive;
        if ($zip->open($zipFilePath) === true) {
            logger()->debug('Extracting ZIP file', [
                'zipFilePath' => $zipFilePath,
                'extractTo' => $extractTo,
            ]);
            $zip->extractTo($extractTo);
            $zip->close();
        } else {
            throw new \Exception('Cannot open zip file at ' . $zipFilePath);
        }
    }
}
