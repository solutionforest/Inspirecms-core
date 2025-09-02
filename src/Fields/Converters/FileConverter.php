<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use SolutionForest\InspireCms\Fields\Dtos\FileDto;
use Throwable;

class FileConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $value = $this->applyLocaleConversion($sourceValue, $locale, $fallbackLocale);

        $disk = $this->fieldTypeConfig->disk ?? config('filesystems.default');
        $directory = $this->fieldTypeConfig->directory;

        try {
            // Ensure is array
            if (! is_array($value)) {
                $value = array_filter([$value]);
            }

            $convertedValues = [];

            foreach ($value as $item) {
                // Already converted
                if ($item instanceof FileDto) {
                    $convertedValues[] = $item;
                } elseif (is_string($item)) {
                    $convertedValues[] = FileDto::fromArray([
                        'path' => $item,
                        'disk' => $disk,
                        'directory' => $directory,
                    ]);
                }
            }

            return $convertedValues;

        } catch (Throwable $th) {
            // fallback as empty array
        }

        return [];
    }
}
