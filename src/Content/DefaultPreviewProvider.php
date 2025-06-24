<?php

namespace SolutionForest\InspireCms\Content;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Pboivin\FilamentPeek\Support\Html;
use SolutionForest\InspireCms\Dtos\ContentDto;
use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
use SolutionForest\InspireCms\Helpers\PropertyTypeHelper;
use SolutionForest\InspireCms\Helpers\TemplateHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class DefaultPreviewProvider implements PreviewProviderInterface
{
    private const PREVIEW_DATA = [
        'isPeekPreviewModal' => true,
    ];

    protected static $previewType = 'internalUrl'; // internalUrl, view

    public function getPeekPreviewType(): string
    {
        return static::$previewType;
    }

    public function configureFilamentPeekAsInternalLink(): void
    {
        if ($this->getPeekPreviewType() === 'internalUrl') {
            config()->set('filament-peek.builderEditor.useInternalPreviewUrl', true);
            config()->set('filament-peek.internalPreviewUrl.enabled', true);
        }
    }

    public function renderContentPreview($documentType, $content, $template, $locale = null, $propertyData = [], $data = [])
    {
        [$htmlContent, $viewData] = $this->prepareContentPreviewContentAndData(
            documentType: $documentType,
            content: $content,
            template: $template,
            locale: $locale,
            propertyData: $propertyData,
            data: $data
        );

        return $this->renderBuilderPreview(
            Blade::render($htmlContent, $viewData, true)
        );
    }

    public function renderTemplatePreview($templateContent, $documentType, $theme = null, $locale = null, $data = [])
    {
        if (empty($templateContent)) {
            return '';
        }

        [$htmlContent, $viewData] = $this->prepareTemplatePreviewContentAndData($templateContent, $documentType, $theme, $locale, $data);

        return $this->renderBuilderPreview(
            Blade::render($templateContent, $viewData)
        );
    }

    protected function prepareContentPreviewContentAndData($documentType, $content, $template, $locale = null, $propertyData = [], $data = [])
    {
        $documentType = $this->findDocumentType($documentType);
        if (! $documentType) {
            Notification::make()
                ->title(__('inspirecms::notification.document_type_not_found.title'))
                ->body(__('inspirecms::notification.document_type_not_found.body'))
                ->danger()
                ->seconds(60)
                ->send();

            return ['Document type not found', []];
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

            return ['Content not found', []];
        }

        $templateContent = $this->findTemplateContent($template) ?? $this->findTemplateContent($documentType->getDefaultTemplate());
        if (empty($templateContent)) {
            Notification::make()
                ->title(__('inspirecms::notification.template_not_found.title'))
                ->body(__('inspirecms::notification.template_not_found.body'))
                ->danger()
                ->seconds(60)
                ->send();

            return ['Template not found', []];
        }

        if ($contentDTO instanceof ContentDto) {
            // Set the locale of the content dto to the active locale
            $contentDTO = $contentDTO->setLocale($locale);
        }

        return [$templateContent, array_merge([
            'locale' => $locale,
            'content' => $contentDTO,
            ...self::PREVIEW_DATA,
        ], $data)];
    }

    protected function prepareTemplatePreviewContentAndData($htmlContent, $documentType, $theme = null, $locale = null, $data = [])
    {
        $documentType = $this->findDocumentType($documentType);
        if (! $documentType) {
            Notification::make()
                ->title(__('inspirecms::notification.document_type_not_found.title'))
                ->body(__('inspirecms::notification.document_type_not_found.body'))
                ->danger()
                ->seconds(60)
                ->send();

            return ['Document type not found', []];
        }

        $contentDTO = $this->buildFakeContentDto($documentType, $locale);

        $viewData = array_merge([
            'content' => $contentDTO,
            'locale' => $contentDTO->getLocale() ?? $locale,
            ...self::PREVIEW_DATA,
        ], $data);

        if ($documentType->isDataType() && ! preg_match("/getComponentWithTheme\(\'(.*?)\'\)/", $htmlContent)) {

            // get the layout
            $layoutName = inspirecms_templates()->getComponentWithTheme(TemplateHelper::getDefaultThemedLayoutComponentName());

            if (view()->exists('components.' . $layoutName)) {

                $viewData = array_merge($viewData, ['slot' => '', 'layoutName' => $layoutName]);

                return ["@extends('components.$layoutName')" . $htmlContent, $viewData];
            }
        }

        return [$htmlContent, $viewData];
    }

    /**
     * @param  int|Model|null  $template
     * @return ?string
     */
    protected function findTemplateContent($template)
    {
        return $this->findTemplate($template)?->getContent() ?? null;
    }

    /**
     * @param  int|Model|null  $template
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function findTemplate($template)
    {
        if (is_null($template)) {
            return null;
        }
        if (! $template instanceof Model) {
            $template = InspireCmsConfig::getTemplateModelClass()::find($template);
        }

        return $template;
    }

    /**
     * @param  int|Model|null  $documentType
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function findDocumentType($documentType)
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
    protected static function getContentModelClass()
    {
        return InspireCmsConfig::getContentModelClass();
    }

    protected function renderBuilderPreview(string $htmlContent)
    {
        return Html::injectPreviewModalStyle($htmlContent);
    }

    /**
     * @param  DocumentType|Model|null  $documentType
     * @param  ?string  $locale
     */
    protected function buildFakeContentDto($documentType, $locale)
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
