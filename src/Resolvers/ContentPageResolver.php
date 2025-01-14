<?php

namespace SolutionForest\InspireCms\Resolvers;

use SolutionForest\InspireCms\Dtos\ContentPageDto;
use SolutionForest\InspireCms\Factories\ContentUrlGeneratorFactory;
use SolutionForest\InspireCms\Generators\UrlGenerators\ContentUrlGeneratorInterface;
use SolutionForest\InspireCms\Services\ContentServiceInterface;
use SolutionForest\InspireCms\Facades\InspireCms;

class ContentPageResolver implements ContentPageResolverInterface
{
    protected ContentServiceInterface $contentService;

    protected ContentUrlGeneratorInterface $urlGenerator;

    public function __construct(ContentServiceInterface $contentService)
    {
        $this->contentService = $contentService;

        $this->urlGenerator = ContentUrlGeneratorFactory::create();
    }

    public function resolve(... $args)
    {
        /**
         * @var ?\Illuminate\Http\Request
         */
        $request = $args[0] ?? request() ?? null;

        if (is_null($request)) {
            return null;
        }

        $locale = $this->urlGenerator->getLocaleFromRequest($request);
        $slug = $this->urlGenerator->getSlugFromRequest($request, $locale);

        if (blank($locale)) {
            $locale = $this->getDefaultLocale();
        }

        $content = $this->findContentAndLangByFullPath($slug);

        if (is_null($content)) {
            return null;
        }
        if (! $content->isPublished() || ! $content->isWebPage()) {
            return null;
        }

        $contentDto = $content->toDto($locale);
        $templateDto = $this->getTemplateForContent($content);

        return ContentPageDto::fromArray([
            'content' => $contentDto,
            'template' => $templateDto,
            'locale' => $locale,
        ]);
    }

    protected function getDefaultLocale(): string
    {
        return InspireCms::getFallbackLanguage()?->code ?? app()->getLocale();
    }

    /**
     * @param  null|\SolutionForest\InspireCms\Models\Contracts\Content & \Illuminate\Database\Eloquent\Model  $content
     * @return null|\SolutionForest\InspireCms\Dtos\TemplateDto
     */
    protected function getTemplateForContent($content)
    {
        $template = $this->contentService->getDefaultTemplateFor($content);

        $theme = inspirecms_templates()->getCurrentTheme();

        return $template?->toDto($theme);
    }

    /**
     * @return null|\SolutionForest\InspireCms\Models\Contracts\Content & \Illuminate\Database\Eloquent\Model
     */
    protected function findContentAndLangByFullPath(?string $fullPath)
    {
        $relations = static::getDtoRelations();

        // ensure the format of full path
        $fullPath = $this->ensureFormatOfFullPath($fullPath ?? '');

        // if the full path is the root path, return the index page
        if (blank(trim($fullPath, '/'))) {
            $content = $this->contentService->findDefaultWebPage();
        } else {
            $content = $this->contentService->findWebPageBySlugPath($fullPath);
        }

        if (is_null($content)) {
            return null;
        }

        $content->loadMissing($relations);

        return $content;
    }

    /**
     * Ensures that the given full path is in the correct format.
     *
     * @param  string  $fullPath  The full path to be formatted.
     * @return string The formatted full path.
     */
    protected function ensureFormatOfFullPath(string $fullPath): string
    {
        return (string) str($fullPath)->trim()->prepend('/');
    }

    protected static function getDtoRelations(): array
    {
        return [
            'documentType.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
        ];
    }
}
