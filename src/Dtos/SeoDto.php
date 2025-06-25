<?php

namespace SolutionForest\InspireCms\Dtos;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;
use SolutionForest\InspireCms\Support\Dtos\MediaAssetDto;

class SeoDto extends BaseDto
{
    /**
     *  The title of the content.
     *
     * @var string
     */
    public $title;

    /**
     *  The description of the content.
     *
     * @var string
     */
    public $description;

    /**
     *  The keywords associated with the content.
     *
     * @var string
     */
    public $keywords;

    /**
     *  The URL of the image associated with the content.
     *
     * @var string
     */
    public $ogImage;

    /**
     * The Open Graph title used for social media sharing.
     *
     * @var string
     */
    public $ogTitle;

    /**
     * The Open Graph description for SEO purposes.
     *
     * @var string|null
     */
    public $ogDescription;

    /**
     *  The URL of the content.
     *
     * @var string
     */
    public $url;

    /**
     *  The type of the content (e.g., article, video).
     *
     * @var string
     */
    public $type;

    /**
     *  The name of the site.
     *
     * @var string
     */
    public $siteName;

    /**
     *  The canonical URL of the content.
     *
     * @var string
     */
    public $canonical;

    /**
     *  Indicates whether the content should not be indexed by search engines.
     *
     * @var string
     */
    public $noIndex;

    /**
     *  Indicates whether search engines should not follow links on the content.
     *
     * @var string
     */
    public $noFollow;

    /**
     * @var Collection<self>|null
     */
    protected $ancestors = null;

    public static function fromArray(array $parameters)
    {
        $mapper = [
            'meta_title' => 'title',
            'meta_description' => 'description',
            'meta_keywords' => 'keywords',
            'og_title' => 'ogTitle',
            'og_description' => 'ogDescription',
            'og_image' => 'ogImage',
            'noindex' => 'noIndex',
            'nofollow' => 'noFollow',
        ];

        foreach ($parameters as $key => $value) {

            switch ($key) {
                case 'og_image':
                    if (is_array($value)) {
                        $value = $value[0] ?? null;
                    }

                    break;
                case 'meta_keywords':
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    break;
            }

            if (array_key_exists($key, $mapper)) {
                $parameters[$mapper[$key]] = $value;
                unset($parameters[$key]);
            }
        }

        $dto = parent::fromArray($parameters);

        $fallbackSeo = InspireCmsConfig::get('frontend.fallback_seo', []);
        $fallbackExtraMapper = [
            'title' => ['ogTitle'],
            'description' => ['ogDescription'],
            'image' => ['ogImage'],
        ];
        if (is_array($fallbackSeo) && ! empty($fallbackSeo)) {
            foreach ($fallbackSeo as $key => $value) {
                $keysToMap = array_unique(array_merge(
                    [$key],
                    $fallbackExtraMapper[$key] ?? []
                ));
                foreach ($keysToMap ?? [] as $mappedKey) {
                    if (! property_exists($dto, $mappedKey)) {
                        continue;
                    }
                    if (! isset($dto->{$mappedKey}) || empty($dto->{$mappedKey})) {
                        $dto->{$mappedKey} = $value;
                    }
                }
            }
        }

        return $dto;
    }

    /**
     * @param  self[]|Collection<self>|null  $ancestors
     * @return self
     */
    public function setAncestors($ancestors)
    {
        $this->ancestors = collect($ancestors ?? [])
            ->whereInstanceOf(static::class)
            ->filter()
            ->values();

        return $this;
    }

    public function getHtml(): Htmlable
    {
        return new HtmlString($this->__toString());
    }

    public function __toString(): string
    {
        $html = '';

        $parentSeo = $this->ancestors->filter()->last();

        if ($this->title) {
            $title = $this->title;
            if ($parentSeo) {
                $title .= ' - ' . $parentSeo->title;
            }
            $html .= "<title>{$title}</title>\n";
        }

        if ($this->description) {
            $html .= "<meta name=\"description\" content=\"{$this->description}\">\n";
        }

        if ($this->keywords) {
            $html .= "<meta name=\"keywords\" content=\"{$this->keywords}\">\n";
        }

        if ($this->ogTitle || $this->title) {
            $ogTitle = $this->ogTitle ?: $this->title;
            if ($parentSeo) {
                $ogTitle .= ' - ' . $parentSeo->title;
            }

            $html .= "<meta property=\"og:title\" content=\"{$ogTitle}\">\n";
        }

        if ($this->ogDescription || $this->description) {
            $ogDescription = $this->ogDescription ?: $this->description;
            $html .= "<meta property=\"og:description\" content=\"{$ogDescription}\">\n";
        }

        if ($odImageUrl = $this->transformImage($this->ogImage)) {
            $html .= "<meta property=\"og:image\" content=\"{$odImageUrl}\">\n";
        }

        if ($this->url) {
            $html .= "<meta property=\"og:url\" content=\"{$this->url}\">\n";
        }

        if ($this->type) {
            $html .= "<meta property=\"og:type\" content=\"{$this->type}\">\n";
        }

        if ($this->siteName) {
            $html .= "<meta property=\"og:site_name\" content=\"{$this->siteName}\">\n";
        }

        if ($this->canonical) {
            $html .= "<link rel=\"canonical\" href=\"{$this->canonical}\">\n";
        }

        $robotsContent = [];
        if ($this->noIndex) {
            $robotsContent[] = 'noindex';
        }
        if ($this->noFollow) {
            $robotsContent[] = 'nofollow';
        }
        if (!empty($robotsContent)) {
            $html .= "<meta name=\"robots\" content=\"" . implode(', ', $robotsContent) . "\">\n";
        }

        return $html;
    }

    protected function transformImage($value)
    {
        // If the value is empty or null, return null
        if (empty($value)) {
            return null;
        }

        // If the value is a string, check if it is a valid URL
        if (is_string($value)) {

            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }
            
            // If not, check if it is a relative URL (e.g., "/storage/images/example.jpg")
            if (str_starts_with($value, '/')) {
                // Assuming the base URL is defined in your configuration
                $baseUrl = config('app.url', 'http://localhost');
                return rtrim($baseUrl, '/') . $value;
            }

            return null;
        }

        // If the value is an array (from MediaPicker)
        if (is_array($value)) {

            try {
                $dto = MediaAssetDto::fromArray($value);
                // Handle the case where the src is a string
                if (filled($dto->src)) {
                    return $this->transformImage($dto->src);
                }
            } catch (\Throwable $th) {
                //
            }
            
            return null;
        }

        // If the value is not a string or an array, return null
        return null;
    }
}
