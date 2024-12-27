<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Blade;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentUrlGeneratorFactory;
use SolutionForest\InspireCms\Generators\UrlGenerators\ContentUrlGeneratorInterface;
use SolutionForest\InspireCms\Services\ContentServiceInterface;

class ContentController extends Controller
{
    protected ContentServiceInterface $contentService;

    protected ContentUrlGeneratorInterface $urlGenerator;

    public function __construct(ContentServiceInterface $contentService)
    {
        $this->contentService = $contentService;

        $this->urlGenerator = ContentUrlGeneratorFactory::create();
    }

    public function __invoke()
    {
        $locale = $this->urlGenerator->getLocaleFromRequest(request());
        $slug = $this->urlGenerator->getSlugFromRequest(request(), $locale);

        if (blank($locale)) {
            $locale = $this->getDefaultLocale();
        }

        $content = $this->findContentAndLangByFullPath($slug);
        if (is_null($content)) {
            abort(404);
        }
        if (! $content->isPublished() || ! $content->isWebPage()) {
            abort(404);
        }

        /**
         * @var \SolutionForest\InspireCms\Dtos\ContentDto $contentDto
         */
        $contentDto = $content->toDto($locale);
        $templateDto = $this->getTemplateForContent($content);

        if (is_null($contentDto) || is_null($templateDto)) {
            abort(404);
        }

        if ($contentDto->isRedirectable()) {

            $redirectUrl = $contentDto->getRedirectUrl($locale);

            if (blank($redirectUrl)) {
                abort(404);
            }

            return redirect($redirectUrl, $contentDto->redirectType ?? 302);

        }

        if (blank($templateDto->content)) {
            abort(404);
        }

        return Blade::render($templateDto->content, [
            'content' => $contentDto,
            'locale' => $locale,
        ]);
    }

    protected function getDefaultLocale(): string
    {
        return InspireCms::getFallbackLanguage()?->code ?? app()->getLocale();
    }

    /**
     * @param null|\SolutionForest\InspireCms\Models\Contracts\Content & \Illuminate\Database\Eloquent\Model $content
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
