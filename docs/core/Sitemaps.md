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
Control which content appears in your sitemap through two available methods:

#### Method 1: Content Management via CMS

1. Navigate to **CMS > Content**
2. Select your content and go to the **"Sitemap"** tab
3. Configure sitemap settings specific to that content:
    - Toggle inclusion in sitemap
    - Set custom priority and change frequency
    - Add special sitemap metadata

#### Method 2: Manual URL Addition

1. Go to **Settings → Sitemap**
2. Manually add specific URLs you want to include
3. For each URL, configure:
    - Priority level
    - Change frequency
    - Last modification date
    - Language/locale information

These two methods can be used complementarily - the CMS method manages existing content pages, while the manual method allows you to add any additional URLs that might not be part of your standard content structure.

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

## Sitemap Customization

### Custom Generator

Create a custom sitemap generator for specialized needs:

```php
namespace App\Services;

use SolutionForest\InspireCms\Sitemap\SitemapGenerator as BaseSitemapGenerator;

class CustomSitemapGenerator extends BaseSitemapGenerator
{
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

### Automatic Generation

The sitemap is automatically regenerated when:
- A sitemap model is created
- A sitemap model is updated
- A sitemap model is deleted

This ensures your sitemap always reflects the latest changes without manual intervention.

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