<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use Composer\InstalledVersions;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Fields\Configs\RichEditor as RichEditorFieldConfig;
use SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\Plugins\ContentPickerRichPlugin;
use SolutionForest\InspireCms\Filament\Forms\Components\RichEditor\Plugins\MediaPickerRichPlugin;

class RichEditorConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        $value = $this->applyLocaleConversion($sourceValue, $locale, $fallbackLocale);

        if (! $value) {
            return $value;
        }

        if (! $this->isFieldTypeTranslatable() && is_array($value)) {
            $value = Arr::first($value);
        }

        return $this->convertHtml($value);
    }

    private function convertHtml($value)
    {
        $disk = $visibility = null;
        $plugins = $textColors = [];

        if (($fieldTypeConfig = $this->getFieldTypeConfig()) instanceof RichEditorFieldConfig) {
            $disk = $fieldTypeConfig->fileAttachmentsDisk;
            $visibility = $fieldTypeConfig->fileAttachmentsVisibility;
            $plugins = $fieldTypeConfig->plugins ?? [];
            $textColors = $fieldTypeConfig->getTextColorsForRichEditor();
        }

        /**
         * @var RichContentRenderer
         */
        $renderer = RichContentRenderer::make($value)
            ->fileAttachmentsDisk($disk)
            ->fileAttachmentsVisibility($visibility)
            ->plugins($plugins)
            ->plugins([
                ContentPickerRichPlugin::make(),
                MediaPickerRichPlugin::make(),
            ]);

        if (
            ($filamentVersion = InstalledVersions::getVersion('filament/filament'))
            && version_compare($filamentVersion, '4.1.0', '>=')
        ) {
            $renderer = $renderer->textColors($textColors);
        }

        return str($renderer->toHtml())->toHtmlString();
    }
}
