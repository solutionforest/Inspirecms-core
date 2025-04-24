# Multilingual Content

InspireCMS provides robust support for creating and managing multilingual content. This guide explains how to set up, create, and manage content in multiple languages.

## Overview

The multilingual system in InspireCMS allows you to:

- Create content in multiple languages
- Manage translations through the admin interface
- Configure language-specific routing
- Switch between languages on the front end
- Use language-specific templates

## Setting Up Languages

### Adding Languages

1. Navigate to **Settings → Languages** in the admin panel
2. Click **Create Language** 
3. Fill in the required fields:
   - **Name**: The display name of the language (e.g., "English")
   - **Locale**: The language code (e.g., "en" for English)
   - **Direction**: Choose "LTR" (Left to Right) for most languages or "RTL" (Right to Left) for languages like Arabic
   - **Is Default**: Check this option for the default language
4. Click **Save** to add the language

### Configuring Available Languages

You can configure which languages are available in your application via the config file:

```php
// config/inspirecms.php
'localization' => [
    'available_locales' => ['en', 'fr', 'zh_CN', 'zh_TW', 'es', 'ja', 'de'],
    'user_preferred_locales' => ['en', 'zh_CN', 'zh_TW'],
],
```

## Creating Multilingual Content

### Translatable Fields

InspireCMS automatically handles translatable fields. When creating or editing content, fields marked as translatable will show a language selector:

```php
// Example field group definition with translatable fields
[
    "slug" => "hero",
    "title" => "Hero Section",
    "fields" => [
        [
            "slug" => "title",
            "label" => "Title",
            "type" => "text",
            "config" => [
                "translatable" => true,  // This makes the field translatable
            ]
        ],
        [
            "slug" => "description",
            "label" => "Description",
            "type" => "textarea",
            "config" => [
                "translatable" => true,  // This makes the field translatable
            ]
        ],
        [
            "slug" => "image",
            "label" => "Image",
            "type" => "mediaPicker",
            "config" => [
                "translatable" => false,  // This field is not translatable
            ]
        ]
    ]
]
```

### Translating Content

To translate content:

1. Create content in your primary language
2. Click the "Translations" tab in the content editor
3. Select the language you want to translate to
4. Fill in the translatable fields for the selected language
5. Save the content

## Accessing Multilingual Content

### Via the Content API

```php
// Get content in the current language
$content = inspirecms_content()->findByRealPath('about-us');
$title = $content->getTitle(); // Uses current application locale

// Get content in a specific language
$frenchTitle = $content->getTitle('fr');
$spanishDescription = $content->getPropertyValue('content', 'description', 'es');

// Check if content has a specific translation
$hasGermanVersion = $content->hasTranslation('de');
```

### In Blade Templates

```php
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

```
https://example.com/en/about-us
https://example.com/fr/a-propos
https://example.com/es/sobre-nosotros
```

### Content Routes

Content routes are automatically generated for each language. You can customize the path for each language:

```php
// Each language can have its own unique path
$content->path; // 'about-us' (default language)
$content->getPath('fr'); // 'a-propos' (French version)
$content->getPath('es'); // 'sobre-nosotros' (Spanish version)
```

## Language Detection

InspireCMS can detect the user's preferred language:

### Based on URL

```php
// Routes are automatically prefixed with the language code
Route::get('/{locale}/about-us', function ($locale) {
    // Sets the application locale based on the URL
    app()->setLocale($locale);
    
    // Your code here
});
```

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

## Working with Translation Files

In addition to content translation, you can translate UI elements:

```php
// resources/lang/en/inspirecms.php
return [
    'dashboard' => 'Dashboard',
    'content' => 'Content',
    // ...
];

// resources/lang/fr/inspirecms.php
return [
    'dashboard' => 'Tableau de bord',
    'content' => 'Contenu',
    // ...
];
```

Access these translations in your templates:

```php
{{ __('inspirecms.dashboard') }} // "Dashboard" or "Tableau de bord" depending on current locale

// Or using the trans directive
@trans('inspirecms.content')
```

## RTL Support

For right-to-left languages like Arabic or Hebrew, InspireCMS provides built-in RTL support:

```php
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

## Best Practices

1. **Default Language**: Always set a default language that will be used as a fallback
2. **Consistent Translation**: Ensure all content is translated across all languages
3. **Language Versioning**: Consider that different languages might require different content updates
4. **Translation Workflows**: Establish workflows for content translation and review
5. **URL Structure**: Maintain consistent URL structures across languages
6. **Language Indicators**: Provide clear language selection options in your UI
7. **RTL Support**: Test thoroughly when supporting right-to-left languages

## Advanced Language Configuration

For more complex language requirements:

```php
// Example service provider extending language capabilities
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SolutionForest\InspireCms\Models\Language;

class LanguageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Add custom language attributes
        Language::resolveRelationUsing('countryFlag', function ($languageModel) {
            return $languageModel->hasOne(CountryFlag::class, 'locale', 'locale');
        });
        
        // Add language-specific middleware for certain routes
        // ...
    }
}
```

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