<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\Resolvers\PublishedContentResolverInterface;
use SolutionForest\InspireCms\Support\Facades\ResolverRegistry;

class FrontendController extends Controller
{
    protected PublishedContentResolverInterface $publishedContentResolver;

    public function __construct()
    {
        $this->publishedContentResolver = ResolverRegistry::get(PublishedContentResolverInterface::class);
    }

    public function __invoke(...$args)
    {
        $dto = $this->publishedContentResolver->resolve(request(), ...$args);

        if (is_null($dto)) {
            abort(404);
        }

        $contentDto = $dto->content;
        $templateDto = $dto->template;
        $locale = $dto->locale;

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

        return $templateDto->render([
            'content' => $contentDto,
            'locale' => $locale,
            ...$dto->parameters,
        ]);
    }
}
