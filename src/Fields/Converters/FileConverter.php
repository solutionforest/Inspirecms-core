<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use SolutionForest\InspireCms\Fields\Dtos\FileDto;

class FileConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $disk = $this->fieldTypeConfig->disk ?? config('filesystems.default');
        $directory = $this->fieldTypeConfig->directory;

        try {
            // Ensure is array
            if (! is_array($sourceValue)) {
                $sourceValue = array_filter([$sourceValue]);
            }

            // Pick value if is translatable
            if ($this->fieldTypeConfig->translatable) {
                $sourceValue = $sourceValue[$locale] ?? $sourceValue[$fallbackLocale] ?? [];
            }

            return collect($sourceValue)
                ->map(fn ($v) => $v instanceof FileDto ? $v : FileDto::fromArray([
                    'path' => $v,
                    'disk' => $disk,
                    'directory' => $directory,
                ]))
                ->all();

        } catch (\Throwable $th) {
            // fallback as empty array
            return [];
        }
    }
}
