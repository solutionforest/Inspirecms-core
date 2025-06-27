---
title: Sitemaps
slug: sitemaps
path: docs/v1/sitemaps
uri: /docs/v1/sitemaps
heading: Sitemaps
brief:
quick_links: []
---

## Overview

By default, InspireCMS creates a sitemap at:

```plaintext
https://your-domain.com/sitemap.xml
```

This sitemap includes:

-   All published content pages
-   Language variations (for multilingual sites)
-   Last modified dates
-   Dynamic update frequency based on content type

---

## Sitemap Configuration

Configure basic sitemap settings in your config file:

```php {title="config/inspirecms.php"}
'sitemap' => [
    'generator' => \SolutionForest\InspireCms\Sitemap\SitemapGenerator::class,
    'file_path' => public_path('sitemap.xml'),
],
```

## Managing Sitemaps

### Accessing Sitemap Settings

Manage sitemap configuration through: **Settings** > **Sitemap**

![Setting_sitemaps](https://inspirecms.net/storage/doc/lLEak5gV2RBUQK7TBsdEXV6ZNMPEAfdO8vsC2afn.png)

### Content Inclusion Rules

Control which content appears in your sitemap through two available methods:

#### Method 1: Content Management via Admin Panel

1. Navigate to **Content**
2. Select your content and go to the **"Sitemap"** tab
3. Configure sitemap settings specific to that content:
    - Toggle inclusion in sitemap
    - Set custom priority and change frequency
    - Add special sitemap metadata

#### Method 2: Manual URL Addition

1. Go to **Settings** > **Sitemap**
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

| Content Type  | Change Frequency | Priority |
| ------------- | ---------------- | -------- |
| Homepage      | weekly           | 1.0      |
| Main sections | weekly           | 0.8      |
| Regular pages | monthly          | 0.5      |
| Archive       | yearly           | 0.3      |

---

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

```php {title="config/inspirecms.php"}
'sitemap' => [
    'generator' => \App\Services\CustomSitemapGenerator::class,
    'file_path' => public_path('sitemap.xml'),
],
```

---

## Sitemap Generation
