<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use SolutionForest\InspireCms\Fields\Dtos\FileDto;

class FileConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $disk = $this->fieldTypeConfig->disk ?? config('filesystems.default');
        $directory = $this->fieldTypeConfig->directory;

        if (! is_array($sourceValue)) {
            $sourceValue = array_filter([$sourceValue]);
        }

        return collect($sourceValue)
            ->map(fn ($path) => FileDto::fromArray([
                'path' => $path,
                'disk' => $disk,
                'directory' => $directory,
            ]))
            ->values()
            ->all();
    }
}
