# Sitemaps

InspireCMS includes built-in sitemap generation to improve SEO and help search engines discover and index your content efficiently. This guide explains how to configure and customize your sitemap.

## Sitemap Overview

A sitemap is an XML file that lists URLs for a site along with metadata about each URL so that search engines can more intelligently crawl the site. InspireCMS can:

- Generate XML sitemaps automatically
- Include all public content
- Set update frequency and priority
- Create multilingual sitemap entries
- Support image and video sitemaps
- Configure custom sitemap entries

## Default Sitemap

By default, InspireCMS creates a sitemap at:

```
https://your-domain.com/sitemap.xml
```

This sitemap includes:
- All published content pages
- Language variations (for multilingual sites)
- Last modified dates
- Dynamic update frequency based on content type

## Sitemap Configuration

Configure basic sitemap settings in your config file:

```php
// config/inspirecms.php
'sitemap' => [
    'generator' => \SolutionForest\InspireCms\Sitemap\SitemapGenerator::class,
    'file_path' => public_path('sitemap.xml'),
],
```

## Managing Sitemaps

### Accessing Sitemap Settings

Manage sitemap configuration through the admin interface:

```
Admin Panel → Settings → Sitemap
```

### Content Inclusion Rules

Control which content appears in your sitemap:

1. Go to **Settings → Sitemap**
2. Under "Content Types" select which document types to include
3. Configure global settings like default frequency and priority
4. Set content-specific sitemap rules

### Prioritizing Content

Set priorities for different content types:

1. Higher priority (closer to 1.0) for important pages
2. Mid-range priority (0.5-0.8) for regular content
3. Lower priority (below 0.5) for less important pages

Example settings:

| Content Type | Change Frequency | Priority |
|--------------|------------------|----------|
| Homepage     | weekly           | 1.0      |
| Main sections| weekly           | 0.8      |
| Regular pages| monthly          | 0.5      |
| Archive      | yearly           | 0.3      |

## Content-Specific Sitemap Settings

Individual content items can have custom sitemap settings:

1. Edit a content item
2. Look for "SEO & Sitemap" settings
3. Configure sitemap options:
   - **Include in Sitemap**: Yes/No
   - **Priority**: 0.0 to 1.0
   - **Change Frequency**: Always, Hourly, Daily, Weekly, Monthly, Yearly, Never

## Sitemap Customization

### Custom Generator

Create a custom sitemap generator for specialized needs:

```php
namespace App\Services;

use SolutionForest\InspireCms\Sitemap\SitemapGenerator as BaseSitemapGenerator;

class CustomSitemapGenerator extends BaseSitemapGenerator
{
    protected function getContentQuery()
    {
        // Customize which content to include
        $query = parent::getContentQuery();
        
        // For example, exclude certain sections
        return $query->where('slug', 'not like', 'internal-%');
    }
    
    protected function getAllAvailableSitemapData(): array
    {
        $data = parent::getAllAvailableSitemapData();
        
        // Add custom URLs
        $data[] = [
            'url' => url('/custom-page'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.8',
            'code' => 'en-US',
        ];
        
        return $data;
    }
}
```

Register your custom generator:

```php
// config/inspirecms.php
'sitemap' => [
    'generator' => \App\Services\CustomSitemapGenerator::class,
    'file_path' => public_path('sitemap.xml'),
],
```

### Multiple Sitemaps

For larger sites, create multiple sitemaps:

```php
namespace App\Services;

use SolutionForest\InspireCms\Sitemap\SitemapGenerator as BaseSitemapGenerator;

class SectionedSitemapGenerator extends BaseSitemapGenerator
{
    public function generate(): void
    {
        // Generate main sitemap index
        $this->generateSitemapIndex();
        
        // Generate section-specific sitemaps
        $this->generateSectionSitemap('blog', 'blog-sitemap.xml');
        $this->generateSectionSitemap('products', 'products-sitemap.xml');
        $this->generateSectionSitemap('services', 'services-sitemap.xml');
    }
    
    protected function generateSitemapIndex(): void
    {
        // Create a sitemap index file
        $content = $this->view('sitemap.index', [
            'sitemaps' => [
                [
                    'loc' => url('blog-sitemap.xml'),
                    'lastmod' => now()->toAtomString(),
                ],
                [
                    'loc' => url('products-sitemap.xml'),
                    'lastmod' => now()->toAtomString(),
                ],
                [
                    'loc' => url('services-sitemap.xml'),
                    'lastmod' => now()->toAtomString(),
                ],
            ]
        ])->render();
        
        file_put_contents(public_path('sitemap.xml'), $content);
    }
    
    protected function generateSectionSitemap(string $section, string $filename): void
    {
        // Your implementation to generate section-specific sitemap
    }
}
```

## Sitemap Generation

### Manual Generation

Generate the sitemap manually:

```bash
php artisan inspirecms:generate-sitemap
```

### Scheduled Generation

Configure automatic sitemap generation:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Generate sitemap daily at midnight
    $schedule->command('inspirecms:generate-sitemap')->daily();
}
```

## Advanced Features

### Image Sitemaps

Include images in your sitemap:

```php
// Example custom sitemap generator with image support
protected function getContentSitemapData($content): array
{
    $data = parent::getContentSitemapData($content);
    
    // Get content images
    $images = [];
    
    if ($heroImage = $content->getPropertyGroup('hero')?->getPropertyData('image')?->getValue()) {
        $images[] = [
            'loc' => $heroImage->getUrl(),
            'title' => $content->getTitle(),
        ];
    }
    
    if ($galleryImages = $content->getPropertyGroup('gallery')?->getPropertyData('images')?->getValue()) {
        foreach ($galleryImages as $image) {
            $images[] = [
                'loc' => $image->getUrl(),
                'title' => $image->title ?? $content->getTitle(),
                'caption' => $image->caption ?? '',
            ];
        }
    }
    
    if (!empty($images)) {
        $data['images'] = $images;
    }
    
    return $data;
}
```

### Video Sitemaps

Include videos in your sitemap:

```php
// Example custom sitemap generator with video support
protected function getContentSitemapData($content): array
{
    $data = parent::getContentSitemapData($content);
    
    // Get content videos
    $videos = [];
    
    if ($featuredVideo = $content->getPropertyGroup('video')?->getPropertyData('featured')?->getValue()) {
        $videos[] = [
            'thumbnail_loc' => $featuredVideo->getThumbnail(),
            'title' => $featuredVideo->title ?? $content->getTitle(),
            'description' => $featuredVideo->description ?? '',
            'content_loc' => $featuredVideo->getUrl(),
            'duration' => $featuredVideo->duration ?? 120,
        ];
    }
    
    if (!empty($videos)) {
        $data['videos'] = $videos;
    }
    
    return $data;
}
```

### Multilingual Sitemaps

For multilingual sites, include alternate language links:

```php
// Example multilingual sitemap implementation
protected function getContentSitemapData($content): array
{
    $data = parent::getContentSitemapData($content);
    
    // Add alternate language versions
    $alternates = [];
    $languages = inspirecms()->getAllAvailableLanguages();
    
    foreach ($languages as $locale => $languageDto) {
        $url = $content->getUrl($locale);
        
        if ($url) {
            $alternates[] = [
                'hreflang' => $locale,
                'href' => $url,
            ];
        }
    }
    
    if (!empty($alternates)) {
        $data['alternates'] = $alternates;
    }
    
    return $data;
}
```

## Best Practices

1. **Regular Updates**: Keep your sitemap fresh by regenerating it when content changes
2. **Prioritize Logically**: Set priorities based on the importance of content
3. **Exclude Private Content**: Don't include login-protected or internal pages
4. **Use Change Frequency**: Set realistic update frequencies for different content types
5. **Submit to Search Engines**: Register your sitemap with Google, Bing, and other search engines
6. **Monitor Coverage**: Check search engine consoles for sitemap coverage issues
7. **Validate XML**: Ensure your sitemap follows the sitemap protocol specifications
8. **Avoid Oversized Sitemaps**: Split large sitemaps if they exceed 50,000 URLs or 50MB

## Troubleshooting

### Common Issues

**Sitemap Not Updating**
Check that:
- Your scheduled task is running
- The sitemap file is writable by the web server
- There are no PHP errors during generation

**Missing Content**
Verify that:
- Content is published and public
- Content is not excluded from the sitemap
- Content passes any custom inclusion rules

**Invalid XML**
Ensure that:
- Special characters are properly encoded
- The sitemap follows the XML sitemap protocol
- All URLs are properly formatted

### Validation Tools

Validate your sitemap with:
- Google Search Console
- [XML Sitemap Validator](https://www.xml-sitemaps.com/validate-xml-sitemap.html)
- [W3C XML Validator](https://validator.w3.org/)