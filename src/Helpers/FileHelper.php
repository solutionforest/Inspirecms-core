<?php

namespace SolutionForest\InspireCms\Helpers;

class FileHelper
{
    public static function buildZipFromFolder(string $pathToZip, string $zipFullResultPath)
    {
        $zip = new \ZipArchive;
        if ($zip->open($zipFullResultPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {

            /** @var \SplFileInfo[] $files */
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pathToZip));

            foreach ($files as $name => $file) {
                $filePath = $file->getPathName();
                $relativePath = substr($filePath, strlen($pathToZip) + 1);

                if (! $file->isDir()) {
                    $zip->addFile($filePath, $relativePath);
                } else {
                    if ($relativePath !== false) {
                        $zip->addEmptyDir($relativePath);
                    }
                }
            }

            $zip->close();
        } else {
            throw new \Exception('Cannot open zip file at ' . $zipFullResultPath);
        }
    }

    public static function unzipFile(string $zipFilePath, string $extractTo)
    {
        $zip = new \ZipArchive;
        try {
            if ($zip->open($zipFilePath) === true) {

                // Extract include folder in the zip file to the destination folder
                // (Exclude the root folder in the zip file)
                for ($i = 0; $i < $zip->numFiles; $i++) {

                    $filename = $zip->getNameIndex($i);
                    $fileInfo = pathinfo($filename);

                    if ($fileInfo['dirname'] == '.') {
                        continue;
                    }

                    $zip->extractTo($extractTo, $filename);
                }

                $zip->close();

            } else {
                throw new \Exception('Cannot open zip file at ' . $zipFilePath);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
