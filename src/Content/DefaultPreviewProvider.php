<?php

namespace SolutionForest\InspireCms\Content;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Pboivin\FilamentPeek\Support\Html;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
use SolutionForest\InspireCms\Helpers\PropertyTypeHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class DefaultPreviewProvider implements PreviewProviderInterface
{
    public function renderContentPreview($documentType, $content, $template, $locale = null, $propertyData = [], $data = [])
    {
        $documentType = $this->findDocumentType($documentType);
        if (! $documentType) {
            Notification::make()
                ->title(__('inspirecms::notification.document_type_not_found.title'))
                ->body(__('inspirecms::notification.document_type_not_found.body'))
                ->danger()
                ->seconds(60)
                ->send();

            return $this->renderBuilderPreview('Document type not found');
        }

        $locale ??= $data['activeLocale'] ?? $data['locale'] ?? null;

        if (isset($data['contentDTO']) && $data['contentDTO'] instanceof ContentDto) {
            $contentDTO = $data['contentDTO'];
            unset($data['contentDTO']);

        } elseif (is_array($content)) {
            $contentDTO = static::getContentModelClass()::toPreviewDto(
                record: $content,
                propertyData: $propertyData,
                locale: $locale,
                documentType: $documentType,
            );
        } else {
            Notification::make()
                ->title(__('inspirecms::notification.content_not_found.title'))
                ->body(__('inspirecms::notification.content_not_found.body'))
                ->danger()
                ->seconds(60)
                ->send();

            return $this->renderBuilderPreview('Content not found');
        }

        $templateContent = $this->findTemplateContent($template) ?? $this->findTemplateContent($documentType->getDefaultTemplate());
        if (empty($templateContent)) {
            Notification::make()
                ->title(__('inspirecms::notification.template_not_found.title'))
                ->body(__('inspirecms::notification.template_not_found.body'))
                ->danger()
                ->seconds(60)
                ->send();

            return $this->renderBuilderPreview('Template not found');
        }

        if ($contentDTO instanceof ContentDto) {
            // Set the locale of the content dto to the active locale
            $contentDTO = $contentDTO->setLocale($locale);
        }

        return $this->renderBuilderPreview(
            Blade::render(
                $templateContent,
                array_merge([
                    'locale' => $locale,
                    'content' => $contentDTO,
                ], $data),
                true
            )
        );
    }

    public function renderTemplatePreview($templateContent, $documentType, $theme = null, $locale = null, $data = [])
    {
        if (empty($templateContent)) {
            return '';
        }

        $documentType = $this->findDocumentType($documentType);
        if (! $documentType) {
            Notification::make()
                ->title(__('inspirecms::notification.document_type_not_found.title'))
                ->body(__('inspirecms::notification.document_type_not_found.body'))
                ->danger()
                ->seconds(60)
                ->send();

            return $this->renderBuilderPreview('Document type not found');
        }

        $contentDTO = $this->buildFakeContentDto($documentType, $locale);

        $viewData = array_merge([
            'content' => $contentDTO,
            'locale' => $contentDTO->getLocale() ?? $locale,
            'isPeekPreviewModal' => true,
        ], $data);

        if ($documentType->isDataType() && ! preg_match("/getComponentWithTheme\(\'(.*?)\'\)/", $templateContent)) {

            // get the layout
            $layoutName = inspirecms_templates()->getComponentWithTheme('layout');

            if (view()->exists('components.' . $layoutName)) {

                $newHtmlContent = Blade::render(
                    "@extends('components.$layoutName')" . $templateContent,
                    array_merge($viewData, ['slot' => '', 'layoutName' => $layoutName]),
                );

                return $this->renderBuilderPreview($newHtmlContent);
            }
        }

        return $this->renderBuilderPreview(
            Blade::render($templateContent, $viewData)
        );
    }

    /**
     * @param  int|Model|null  $template
     * @return ?string
     */
    private function findTemplateContent($template)
    {
        if (is_null($template)) {
            return null;
        }
        if (! $template instanceof Model) {
            $template = InspireCmsConfig::getTemplateModelClass()::find($template);
        }

        return $template?->getContent() ?? null;
    }

    /**
     * @param  int|Model|null  $documentType
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function findDocumentType($documentType)
    {
        if (is_null($documentType)) {
            return null;
        }
        if (! $documentType instanceof Model) {
            $documentType = InspireCmsConfig::getDocumentTypeModelClass()::find($documentType);
        }

        return $documentType;
    }

    /**
     * @return class-string<Content|Model>
     */
    private static function getContentModelClass()
    {
        return InspireCmsConfig::getContentModelClass();
    }

    private function renderBuilderPreview(string $htmlContent)
    {
        return Html::injectPreviewModalStyle($htmlContent);
    }

    /**
     * @param  DocumentType|Model|null  $documentType
     * @param  ?string  $locale
     */
    private function buildFakeContentDto($documentType, $locale)
    {
        if (is_null($documentType)) {
            return null;
        }

        $availableLanguages = inspirecms()->getAllAvailableLanguages();
        $availableLocales = collect($availableLanguages)->map(fn ($lang) => $lang->code)->toArray();
        $fallbackLocale = array_key_first($availableLocales) ?? 'en';

        $propertyTypes = collect($documentType?->fields)->map(fn ($field) => $field->toDto())->whereInstanceOf(PropertyTypeDto::class);
        $propertyData = $propertyTypes
            ->mapToGroups(fn (PropertyTypeDto $ptDTO) => [$ptDTO->group => PropertyTypeHelper::fakeDisplayValueForPropertyType($ptDTO, array_keys($availableLocales))])
            ->map(fn ($list) => collect($list)->filter()->values()->all())
            ->toArray();

        $dto = static::getContentModelClass()::toPreviewDto(
            record: app(static::getContentModelClass(), [
                'attributes' => [
                    'title' => collect($availableLocales)->map(fn () => fake()->slug(1))->toArray(),
                    'slug' => fake()->slug(1),
                ],
            ]),
            propertyData: $propertyData,
            locale: $locale ?? $fallbackLocale,
            documentType: $documentType,
        );

        $dto->propertyTypes = $propertyTypes;
        $dto->setLocale($locale ?? $fallbackLocale);
        $dto->setFallbackLocale($fallbackLocale);
        $dto->setAvailableLocales($availableLocales);

        return $dto;
    }
}
