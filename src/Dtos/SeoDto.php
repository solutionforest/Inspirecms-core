<?php

namespace SolutionForest\InspireCms\Dtos;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

class SeoDto extends BaseDto
{
    /**
     *  The title of the content.
     * @var string
     */
    public $title;
    /**
     *  The description of the content.
     * @var string
     */
    public $description;
    /**
     *  The keywords associated with the content.
     * @var string
     */
    public $keywords;
    /**
     *  The URL of the image associated with the content.
     * @var string
     */
    public $image;
    /**
     *  The URL of the content.
     * @var string
     */
    public $url;
    /**
     *  The type of the content (e.g., article, video).
     * @var string
     */
    public $type;
    /**
     *  The name of the site.
     * @var string
     */
    public $siteName;
    /**
     *  The locale of the content.
     * @var string
     */
    public $locale;
    /**
     *  The published time of the content.
     * @var string
     */
    public $publishedTime;
    /**
     *  The last modified time of the content.
     * @var string
     */
    public $modifiedTime;
    /**
     *  The author of the content.
     * @var string
     */
    public $author;
    /**
     *  The section of the site where the content is located.
     * @var string
     */
    public $section;
    /**
     *  The tag associated with the content.
     * @var string
     */
    public $tag;
    /**
     *  The category of the content.
     * @var string
     */
    public $category;
    /**
     *  The canonical URL of the content.
     * @var string
     */
    public $canonical;
    /**
     *  The alternate URL of the content.
     * @var string
     */
    public $alternate;
    /**
     *  The AMP HTML version of the content.
     * @var string
     */
    public $ampHtml;
    /**
     *  The AMP version of the content.
     * @var string
     */
    public $amp;
    /**
     *  Indicates whether the content should not be indexed by search engines.
     * @var string
     */
    public $noIndex;
    /**
     *  Indicates whether search engines should not follow links on the content.
     * @var string
     */
    public $noFollow;
    /**
     *  Indicates whether the content should not be archived by search engines.
     * @var string
     */
    public $noArchive;
    /**
     *  Indicates whether search engines should not show a snippet of the content.
     * @var string
     */
    public $noSnippet;
    /**
     *  Indicates whether the content should not be included in the Open Directory Project.
     * @var string
     */
    public $noOdp;
    /**
     *  Indicates whether the content should not be included in Yahoo Directory.
     * @var string
     */
    public $noYdir;
    /**
     *  Indicates whether images on the content should not be indexed by search engines.
     * @var string
     */
    public $noImageIndex;
    /**
     *  Indicates whether the content should not be translated by search engines.
     * @var string
     */
    public $noTranslate;
    /**
     *  Indicates whether the content should not be cached by search engines.
     * @var string
     */
    public $noCache;

    public function __toString(): string
    {
        $html = '';

        if ($this->title) {
            $html .= "<title>{$this->title}</title>\n";
        }

        if ($this->description) {
            $html .= "<meta name=\"description\" content=\"{$this->description}\">\n";
        }

        if ($this->keywords) {
            $html .= "<meta name=\"keywords\" content=\"{$this->keywords}\">\n";
        }

        if ($this->image) {
            $html .= "<meta property=\"og:image\" content=\"{$this->image}\">\n";
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

        if ($this->locale) {
            $html .= "<meta property=\"og:locale\" content=\"{$this->locale}\">\n";
        }

        if ($this->publishedTime) {
            $html .= "<meta property=\"article:published_time\" content=\"{$this->publishedTime}\">\n";
        }

        if ($this->modifiedTime) {
            $html .= "<meta property=\"article:modified_time\" content=\"{$this->modifiedTime}\">\n";
        }

        if ($this->author) {
            $html .= "<meta property=\"article:author\" content=\"{$this->author}\">\n";
        }

        if ($this->section) {
            $html .= "<meta property=\"article:section\" content=\"{$this->section}\">\n";
        }

        if ($this->tag) {
            $html .= "<meta property=\"article:tag\" content=\"{$this->tag}\">\n";
        }

        if ($this->category) {
            $html .= "<meta property=\"article:category\" content=\"{$this->category}\">\n";
        }

        if ($this->canonical) {
            $html .= "<link rel=\"canonical\" href=\"{$this->canonical}\">\n";
        }

        if ($this->alternate) {
            $html .= "<link rel=\"alternate\" href=\"{$this->alternate}\">\n";
        }

        if ($this->ampHtml) {
            $html .= "<link rel=\"amphtml\" href=\"{$this->ampHtml}\">\n";
        }

        if ($this->amp) {
            $html .= "<link rel=\"amp\" href=\"{$this->amp}\">\n";
        }

        if ($this->noIndex) {
            $html .= "<meta name=\"robots\" content=\"noindex\">\n";
        }

        if ($this->noFollow) {
            $html .= "<meta name=\"robots\" content=\"nofollow\">\n";
        }

        if ($this->noArchive) {
            $html .= "<meta name=\"robots\" content=\"noarchive\">\n";
        }

        if ($this->noSnippet) {
            $html .= "<meta name=\"robots\" content=\"nosnippet\">\n";
        }

        if ($this->noOdp) {
            $html .= "<meta name=\"robots\" content=\"noodp\">\n";
        }

        if ($this->noYdir) {
            $html .= "<meta name=\"robots\" content=\"noydir\">\n";
        }

        if ($this->noImageIndex) {
            $html .= "<meta name=\"robots\" content=\"noimageindex\">\n";
        }

        if ($this->noTranslate) {
            $html .= "<meta name=\"robots\" content=\"notranslate\">\n";
        }

        if ($this->noCache) {
            $html .= "<meta name=\"robots\" content=\"nocache\">\n";
        }

        return $html;
    }
}
