<?php

namespace SolutionForest\InspireCms\Http\Controllers;

use Illuminate\Routing\Controller;
use SolutionForest\InspireCms\Models\Contracts\SiteMap;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class SitemapController extends Controller
{
    public function __invoke()
    {
        // todo: cache the sitemap
        $sitemap = InspireCmsConfig::getSiteMapModelClass()::with('model')->get();
        
        $items = collect($sitemap)
            ->whereInstanceOf(SiteMap::class)
            ->map(fn (SiteMap $item) => $item->generateSitemapItem())->toArray();

        $xml = $this->generateSitemap($items);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');

    }

    private function generateSitemap($items)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($items as $arr) {
            if ((!isset($arr['url']) || empty($arr['url'])) || !isset($arr['lastmod']) || !isset($arr['changefreq']) || !isset($arr['priority'])) {
                continue;
            }
            $urlTag = $xml->addChild('url');
            $urlTag->addChild('url', $arr['url']);
            $urlTag->addChild('lastmod', $arr['lastmod']);
            $urlTag->addChild('changefreq', $arr['changefreq']);
            $urlTag->addChild('priority', $arr['priority']);
        }

        return $xml->asXML();
    }
}
