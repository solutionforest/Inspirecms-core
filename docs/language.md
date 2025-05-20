---
title: Language
slug: language
path: docs/v1/language
uri: /docs/1.x/language
---
# Language

InspireCMS provides comprehensive tools for creating multilingual websites and managing translations. This guide covers language configuration, content translation, and multilingual site setup.

---

## Overview

The language system in InspireCMS allows you to:

- Define multiple languages for your site
- Translate content into different languages
- Manage language-specific URLs
- Switch between languages on both admin and frontend interfaces
- Create language-specific templates

---

## Configuring Languages

### Managing Languages

Languages are managed through the admin interface:

1. Navigate to **Settings** > **Languages**
2. Here you can:
   - View existing languages
   - Add new languages
   - Edit language settings
   - Set the default language

### Adding a New Language

1. Go to **Settings** > **Languages**
2. Click **New Language**
3. Fill in the required information:
   - **Code**: Standard language code (e.g., 'fr' for French)
   - **Is Default**: Whether this is the default language
4. Click **Save**

### Setting the Default Language

The default language is used when:

- A user first visits your site without a language preference
- A requested content translation doesn't exist
- Fallback content is required

To change the default language:

1. Go to **Settings** > **Languages**
2. Find the language you want to make default
3. Toggle the "Default" checkbox or edit and check "Default"

---

## Translating Content

InspireCMS manages content translations through a flexible system based on locale keys.

### Creating Multilingual Content

When creating or editing content:

1. Look for the language selector (often near the top of the form)
2. Select the language you want to create/edit content for
3. Enter content in that language
4. Switch to another language to provide translations
5. Fields that are translatable will show for each language

### Translatable Fields

By default, the following fields are typically translatable:

- Content title
- Content body/main text
- Meta description
- URL slugs (can be different per language)
- Custom fields marked as translatable

### Setting Up Translatable Fields

When creating custom fields, enable translation support:

```php
// In your field group definition
use SolutionForest\InspireCms\Fields\Configs\Attributes\Translatable;

#[Translatable(true)]
class YourFieldConfig extends FieldTypeBaseConfig implements FieldTypeConfig
{
    // Field configuration
}
```

Or when configuring fields through the admin interface:

1. Go to **Settings** > **Custom Fields** > **[Your Field Group]**
2. Edit the field you want to make translatable
3. Enable the "Translatable" option
4. Save the field configuration

---

## URL Structure for Multilingual Sites

InspireCMS supports different URL strategies for multilingual content:

### 1. Language Prefix URLs

```plaintext
/en/about-us
/fr/a-propos
/es/sobre-nosotros
```

This is the default and most common approach, adding the language code to the URL.

### 2. Custom URL Structure

#### 2.1. Configure Frontend Segment Provider

For advanced URL handling, implement a custom segment provider:

```php {title="config/inspirecms.php"}
'frontend' => [
    'segment_provider' => \App\Services\CustomLanguageSegmentProvider::class,
],
```

Then define your provider:

```php
namespace App\Services;

use SolutionForest\InspireCms\Content\SegmentProviderInterface;

class CustomLanguageSegmentProvider implements SegmentProviderInterface
{
    public function getSegments(string $uri): array
    {
        // Remove query parameters if present
        $path = parse_url($uri, PHP_URL_PATH) ?? $uri;
        $path = trim($path, '/');
        
        if (empty($path)) {
            return [];
        }
        
        $segments = explode('/', $path);
        
        // Check if the first segment is a valid language code
        $availableLocales = config('inspirecms.available_locales', ['en']);
        
        if (in_array($segments[0], $availableLocales)) {
            // If first segment is a language code, extract it from segments
            $locale = array_shift($segments);
            app()->setLocale($locale);
        }
        
        return $segments;
    }

    public function getLocaleFromDefaultRoute($route)
    {
        // Option 1: Language Prefix URLs
        // Extract from URL path format: /en/about, /fr/contact, etc.
        $uri = request()->getRequestUri();
        $firstSegment = explode('/', trim($uri, '/'))[0] ?? null;
        
        $availableLocales = config('inspirecms.available_locales', ['en']);
        if ($firstSegment && in_array($firstSegment, $availableLocales)) {
            return $firstSegment;
        }
        
        // Option 2: Domain-based Languages
        // Extract from domain format: en.example.com, fr.example.com, etc.
        $host = request()->getHost();
        $domainMapping = json_decode(env('INSPIRECMS_LANG_DOMAIN_MAPPING', '{}'), true);
        
        // Check if current domain is mapped to a language
        foreach ($domainMapping as $locale => $domain) {
            if ($domain === $host) {
                return $locale;
            }
        }
        
        // Check for subdomain-based locale
        $subdomain = explode('.', $host)[0] ?? null;
        if ($subdomain && in_array($subdomain, $availableLocales)) {
            return $subdomain;
        }
        
        // Return default locale if no match found
        return app()->getLocale();
    }
}
```

#### 2.2 Configure Published Content Resolver

For multilingual sites with custom URL structures, you may need a custom content resolver to determine which content to display based on the current language:

```php {ttile="config/inspirecms.php"}
'resolvers' => [
    'published_content' => \App\Services\MultilingualContentResolver::class,
],
```

Create your custom resolver:

```php
namespace App\Services;

use SolutionForest\InspireCms\Dtos\PublishedContentDto;
use SolutionForest\InspireCms\Services\ContentServiceInterface;
use SolutionForest\InspireCms\Resolvers\PublishedContentResolverInterface;

class MultilingualContentResolver implements PublishedContentResolverInterface
{
    protected $contentService;
    
    public function __construct(ContentServiceInterface $contentService)
    {
        $this->contentService = $contentService;
    }

    protected function getContentAndLocaleByRoute($route)
    {
        //
    }
}
```

This resolver gives you fine-grained control over how InspireCMS resolves URLs to content for different languages, allowing you to:

- Handle different URL patterns per language
- Implement language-specific content resolution logic
- Create custom fallback strategies when content isn't available in the requested language
- Support domain or subdomain-based language routing

For complex multilingual architectures, you can combine this with the segment provider to create a fully customized routing solution.

---

## Language Switching

### Adding a Language Switcher

InspireCMS provides helper functions to create language switchers:

```blade
<!-- In your template -->
<div class="language-switcher">
    @foreach(inspirecms()->getAllAvailableLanguages() as $locale => $languageDto)
        <a 
            href="{{ request()->fullUrlWithQuery(['locale' => $locale]) }}" 
            class="{{ app()->getLocale() === $locale ? 'active' : '' }}">
            {{ $languageDto->getLabel() }}
        </a>
    @endforeach
</div>
```

## Language-Specific Templates

For cases where different languages need different layouts:

### Using Language-Specific Components

```blade
@php
    $locale = app()->getLocale();
    $componentName = "hero-{$locale}";
    
    // Fallback to default component if language-specific one doesn't exist
    if (!view()->exists("components.{$componentName}")) {
        $componentName = "hero";
    }
@endphp

<x-dynamic-component :component="$componentName" :content="$content" />
```

### Conditional Content by Language

```blade
@if(app()->getLocale() === 'zh_CN')
    <div class="chinese-specific-content">
        <!-- Chinese-specific content -->
    </div>
@elseif(app()->getLocale() === 'ar')
    <div class="arabic-specific-content" dir="rtl">
        <!-- Arabic-specific content with RTL direction -->
    </div>
@else
    <div class="default-content">
        <!-- Default content for other languages -->
    </div>
@endif
```

---

## Translation Caching

InspireCMS caches translations for performance:

```php {title="config/inspirecms.php"}
'cache' => [
    'languages' => [
        'key' => 'inspirecms.languages',
        'ttl' => 60 * 60 * 24, // 24 hours in seconds
    ],
],
```

Clear the language cache after making significant changes to language settings:

```bash
php artisan cache:clear
```

---

## Best Practices

- **Start with Default Language**: Create content in your default language first
- **Consistent URLs**: Use consistent URL strategies across languages
- **Translation Workflow**: Establish a process for content translation
- **Language Variants**: Consider language variants (e.g., PT-BR vs. PT-PT) for targeted audiences
- **User Experience**: Make language switching obvious and consistent
- **SEO Optimization**: Use hreflang tags to help search engines understand your multilingual content

