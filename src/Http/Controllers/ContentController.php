<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\Factories\ContentPathGeneratorFactory;
use SolutionForest\InspireCms\Factories\ContentUrlGeneratorFactory;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Services\ContentServiceInterface;
use SolutionForest\InspireCms\Support\PathGenerators\ContentPathGeneratorInterface;
use SolutionForest\InspireCms\Support\UrlGenerators\ContentUrlGeneratorInterface;

class ContentController extends Controller
{
    protected ContentServiceInterface $contentService;

    protected ContentPathGeneratorInterface $pathGenerator;

    protected ContentUrlGeneratorInterface $urlGenerator;

    public function __construct(ContentServiceInterface $contentService) 
    {
        $this->contentService = $contentService;

        $this->pathGenerator = ContentPathGeneratorFactory::create();
        $this->urlGenerator = ContentUrlGeneratorFactory::create();
    }

    public function __invoke()
    {
        $locale = $this->urlGenerator->getLocaleFromRequest(request());
        $slug = $this->pathGenerator->getSlugFromRequest(request(), $locale);

        // Redirect to the localized URL if needed
        if (blank($locale)) {
            return $this->redirectToLocalizedUrl($slug ?? '', request()->getDefaultLocale());
        }


        // Is index page
        if (blank($slug)) {
            // todo: add index page setting
            $slug = 'home';
        }

        $content = $this->findContent($slug);
        
        $contentDto = $content->toDto($locale);

        $view = $this->getTemplateView($content);

        return view($view, [
            'content' => $contentDto,
        ]);
    }

    protected function findContent(string $slug): Content
    {
        // Remove the leading and trailing slashes
        $fullPath = trim($slug, '/');

        $content = $this->contentService->searchOne($fullPath);

        if (is_null($content) || ! $content instanceof Content) {
            abort(404);
        }

        if (!$content->isPublished() || ! $content->isWebPage()) {
            abort(404);
        }

        return $content;
    }

    protected function getTemplateView(Content $content)
    {
        $template = $content->getDefaultTemplate() ?? $content->documentType?->getDefaultTemplate();

        if (! $template) {
            abort(404);
        }

        return $template->getViewFullName();
    }

    /**
     * Redirects to a localized URL based on the provided slug and locale.
     *
     * @param string $slug The slug of the content.
     * @param string $locale The locale to redirect to.
     * @return \Illuminate\Http\RedirectResponse The redirect response to the localized URL.
     */
    protected function redirectToLocalizedUrl(string $slug, string $locale): \Illuminate\Http\RedirectResponse
    {
        $url = $this->urlGenerator->getLocalizedUrl($slug, $locale);

        return redirect($url);
    }
}
