<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Factories\ContentPathGeneratorFactory;
use SolutionForest\InspireCms\Factories\ContentUrlGeneratorFactory;
use SolutionForest\InspireCms\Generators\PathGenerators\ContentPathGeneratorInterface;
use SolutionForest\InspireCms\Generators\UrlGenerators\ContentUrlGeneratorInterface;
use SolutionForest\InspireCms\Services\PageServiceInterface;

class ContentController extends Controller
{
    protected PageServiceInterface $pageService;

    protected ContentPathGeneratorInterface $pathGenerator;

    protected ContentUrlGeneratorInterface $urlGenerator;

    public function __construct(PageServiceInterface $pageService)
    {
        $this->pageService = $pageService;

        $this->pathGenerator = ContentPathGeneratorFactory::create();
        $this->urlGenerator = ContentUrlGeneratorFactory::create();
    }

    public function __invoke()
    {
        $locale = $this->urlGenerator->getLocaleFromRequest(request());
        $slug = $this->pathGenerator->getSlugFromRequest(request(), $locale);

        // Redirect to the localized URL if needed
        if (blank($locale)) {
            return $this->redirectToLocalizedUrl($slug ?? '', $this->getDefaultLocale());
        }

        // Is index page
        if (blank($slug)) {
            $slug = '/';
        }

        [$contentDto, $view] = $this->pageService->findPublishedContentAndView($slug, $locale);

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
        ]);
    }

    /**
     * Redirects to a localized URL based on the provided slug and locale.
     *
     * @param  string  $slug  The slug of the content.
     * @param  string  $locale  The locale to redirect to.
     * @return \Illuminate\Http\RedirectResponse The redirect response to the localized URL.
     */
    protected function redirectToLocalizedUrl(string $slug, string $locale): \Illuminate\Http\RedirectResponse
    {
        $url = $this->urlGenerator->getLocalizedUrl($slug, $locale);

        return redirect($url);
    }

    protected function getDefaultLocale(): string
    {
        return InspireCms::getFallbackLanguage()?->locale ?? app()->getLocale() ?? '';
    }
}
