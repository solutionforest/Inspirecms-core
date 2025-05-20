---
title: Multilingual Content
slug: multilingual-content
path: docs/v1/multilingual-content
uri: /docs/1.x/multilingual-content
---
# Multilingual Content

InspireCMS provides robust support for creating and managing multilingual content. This guide explains how to set up, create, and manage content in multiple languages.

---

## Overview

The multilingual system in InspireCMS allows you to:

- Create content in multiple languages
- Manage translations through the admin interface
- Configure language-specific routing
- Switch between languages on the front end
- Use language-specific templates

---

## Setting Up Languages

### Adding Languages

1. Navigate to **Settings** > **Languages** in the admin panel
2. Click **Create Language** 
3. Fill in the required fields:
   - **Code**: The language code (e.g., "en" for English)
   - **Is Default**: Check this option for the default language
4. Click **Save** to add the language

---

## Creating Multilingual Content

### Translatable Fields

InspireCMS allows you to mark specific content fields as translatable directly through the admin panel:

1. Navigate to **Document Types** in the admin panel
2. Select the content type you want to modify
3. Edit or create a field group
4. For each field that should support multiple languages:
    - In the field settings, check the **Translatable** checkbox
    - Save your changes

Fields marked as translatable will automatically display language-specific inputs when creating or editing content. When editing content with translatable fields:

1. The default language version appears first
2. Language tabs appear at the top of each translatable field
3. Click on a language tab to enter content for that specific language
4. Non-translatable fields will remain the same across all languages

This allows you to maintain language-specific content while keeping structure consistent across translations.

### Translating Content

To translate content:

1. Create content in your primary language
2. Select the language you want to translate to
3. Fill in the translatable fields for the selected language
4. Save the content

---

## Accessing Multilingual Content

### Via the Content API

```php
// Get content in the current language
$content = inspirecms_content()->findByRealPath('about-us');
$title = $content->getTitle(); // Uses current application locale

// Get content in a specific language
$frenchTitle = $content->getTitle('fr');
$spanishDescription = $content->getPropertyValue('content', 'description', 'es');
```

### In Blade Templates

```blade
<!-- Basic property access in current language -->
<h1>@property('hero', 'title')</h1>

<!-- Access in specific language -->
<h1>{{ $content->getTitle('fr') }}</h1>
<p>{{ $content->getPropertyValue('hero', 'description', 'fr') }}</p>

<!-- Language switcher example -->
<div class="language-switcher">
    @foreach(inspirecms()->getAllAvailableLanguages() as $locale => $langDto)
        <a href="{{ url($locale . '/' . $content->getPath()) }}" class="{{ app()->getLocale() == $locale ? 'active' : '' }}">
            {{ $langDto->getLabel() }}
        </a>
    @endforeach
</div>
```

## URL Structure for Multilingual Sites

InspireCMS automatically prefixes URLs with the language code:

```plaintext
https://example.com/en/about-us
https://example.com/fr/a-propos
https://example.com/es/sobre-nosotros
```

---

## Language Detection

InspireCMS can detect the user's preferred language through multiple methods:

### Based on URL

InspireCMS automatically handles language detection from URL patterns:

```plaintext
/en/about-us
/fr/a-propos
```

#### Content Resolution by Route Pattern

InspireCMS provides a built-in mechanism to find content by URL pattern while detecting the language:

```php
// In a controller or middleware
$uri = 'about-us';
$result = inspirecms_content()->findByRoutePatternWithLangId(
    uri: $uri, 
    isDefaultRoutePattern: true,  // Set to true for default routes
    isPublished: true
);

foreach ($result as $item) {
    $content = $item['content'];
    $languageId = $item['language_id'];
    
    // Now you have both the content and its associated language ID
    $language = \SolutionForest\InspireCms\InspireCmsConfig::getLanguageModelClass()::find($languageId);
    
    // Set application locale based on the content's language
    if ($language) {
        app()->setLocale($language->locale);
    }
    
    // Render the content with the correct locale
    return view('content', compact('content'));
}
```

This approach provides several advantages:
- Automatically resolves content based on the URL pattern
- Returns the appropriate language ID for each content item
- Allows for language-specific routing and content presentation
- Handles multilingual URLs efficiently

### Based on Browser Preferences

```php
// Example middleware for language detection
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SolutionForest\InspireCms\Facades\InspireCms;

class DetectLanguage
{
    public function handle(Request $request, Closure $next)
    {
        // If no language in URL, detect from browser
        if (!$request->segment(1) || !array_key_exists($request->segment(1), InspireCms::getAllAvailableLanguages())) {
            $browserLocales = $request->getLanguages();
            $availableLocales = array_keys(InspireCms::getAllAvailableLanguages());
            
            // Find the first browser locale that matches our available locales
            foreach ($browserLocales as $browserLocale) {
                $locale = substr($browserLocale, 0, 2); // Get 2-letter code
                if (in_array($locale, $availableLocales)) {
                    return redirect()->to($locale . $request->getRequestUri());
                }
            }
            
            // Default to primary language if no match
            $defaultLocale = InspireCms::getFallbackLanguage()?->getLocale() ?? 'en';
            return redirect()->to($defaultLocale . $request->getRequestUri());
        }
        
        return $next($request);
    }
}
```

---

## Fallback Languages

When content is not available in a requested language, InspireCMS can fall back to a default language:

```php
// Get fallback language
$fallbackLanguage = inspirecms()->getFallbackLanguage();

// Access content with fallback
$title = $content->getTitle($locale) ?? $content->getTitle($fallbackLanguage->getLocale());

// Or use the built-in fallback
$title = $content->getTitle($locale); // Automatically falls back if translation not available
```

---

## RTL Support

For right-to-left languages like Arabic or Hebrew, InspireCMS provides built-in RTL support:

```blade
<!-- In your layout -->
@php
    $direction = in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']) ? 'rtl' : 'ltr';
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $direction }}">
    <!-- Your HTML content -->
</html>

<!-- Add RTL-specific styles -->
@if($direction === 'rtl')
    <link rel="stylesheet" href="{{ asset('css/rtl.css') }}">
@endif
```

---

## Best Practices

1. **Default Language**: Always set a default language that will be used as a fallback
2. **Consistent Translation**: Ensure all content is translated across all languages
3. **Language Versioning**: Consider that different languages might require different content updates
4. **Translation Workflows**: Establish workflows for content translation and review
5. **URL Structure**: Maintain consistent URL structures across languages
6. **Language Indicators**: Provide clear language selection options in your UI
7. **RTL Support**: Test thoroughly when supporting right-to-left languages

---

## Troubleshooting Common Issues

### Missing Translations

If translations are missing:

1. Check if the language is properly configured in Settings → Languages
2. Verify that the content has been translated to the target language
3. Ensure the language file exists in resources/lang/{locale}/

### URL Issues

If language URLs are not working:

1. Make sure your routes are properly configured for prefixed locales
2. Check for any conflicts with route names or patterns
3. Verify that your middleware is handling language detection correctly

### RTL Layout Problems

If RTL layouts are not displaying correctly:

1. Ensure the HTML tag has `dir="rtl"` attribute
2. Check that RTL stylesheets are being loaded
3. Verify that UI components support RTL layout