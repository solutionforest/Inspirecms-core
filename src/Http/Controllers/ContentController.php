<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Blade;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentUrlGeneratorFactory;
use SolutionForest\InspireCms\Generators\UrlGenerators\ContentUrlGeneratorInterface;
use SolutionForest\InspireCms\Services\PageServiceInterface;

class ContentController extends Controller
{
    protected PageServiceInterface $pageService;

    protected ContentUrlGeneratorInterface $urlGenerator;

    public function __construct(PageServiceInterface $pageService)
    {
        $this->pageService = $pageService;

        $this->urlGenerator = ContentUrlGeneratorFactory::create();
    }

    public function __invoke()
    {
        $locale = $this->urlGenerator->getLocaleFromRequest(request());
        $slug = $this->urlGenerator->getSlugFromRequest(request(), $locale);

        // Redirect to the localized URL if needed
        if (blank($locale)) {
            $locale = $this->getDefaultLocale();
        }

        [$contentDto, $templateDto] = $this->pageService->findContentAndTemplate($slug, $locale);

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
}
