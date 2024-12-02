<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
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

        [$contentDto, $view] = $this->pageService->findContentAndView($slug, $locale);

        if (is_null($contentDto) || is_null($view)) {
            abort(404);
        }

        if ($contentDto->isRedirectable()) {

            $redirectUrl = $contentDto->getRedirectUrl($locale);

            if (blank($redirectUrl)) {
                abort(404);
            }

            return redirect($redirectUrl, $contentDto->redirectType ?? 302);

        }

        return view($view, [
            'content' => $contentDto,
        ])->with([
            'locale' => $locale,
        ]);
    }

    protected function getDefaultLocale(): string
    {
        return InspireCms::getFallbackLanguage()?->code ?? app()->getLocale();
    }
}
