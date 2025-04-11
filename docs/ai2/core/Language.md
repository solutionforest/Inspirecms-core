# Language Management

InspireCMS provides comprehensive tools for creating multilingual websites and managing translations. This guide covers language configuration, content translation, and multilingual site setup.

## Language System Overview

The language system in InspireCMS allows you to:

- Define multiple languages for your site
- Translate content into different languages
- Manage language-specific URLs
- Switch between languages on both admin and frontend interfaces
- Create language-specific templates

## Configuring Languages

### Available Languages

Configure the available languages in your configuration file:

```php
// config/inspirecms.php
'available_locales' => [
    'en',     // English
    'fr',     // French
    'zh_CN',  // Simplified Chinese
    'zh_TW',  // Traditional Chinese
    'es',     // Spanish
    // Add additional languages as needed
],
```

### Managing Languages

Languages are managed through the admin interface:

1. Navigate to **Settings → Languages**
2. Here you can:
   - View existing languages
   - Add new languages
   - Edit language settings
   - Set the default language
   - Enable/disable languages

### Adding a New Language

1. Go to **Settings → Languages**
2. Click **Create Language**
3. Fill in the required information:
   - **Locale Code**: Standard language code (e.g., 'fr' for French)
   - **Name**: Language name in its native form (e.g., 'Français')
   - **English Name**: Language name in English (e.g., 'French')
   - **Direction**: LTR (left-to-right) or RTL (right-to-left)
   - **Is Default**: Whether this is the default language
   - **Is Active**: Whether the language is currently available on the site
4. Click **Save**

### Setting the Default Language

The default language is used when:

- A user first visits your site without a language preference
- A requested content translation doesn't exist
- Fallback content is required

To change the default language:

1. Go to **Settings → Languages**
2. Find the language you want to make default
3. Click the "Make Default" button or edit and check "Is Default"

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

1. Go to **Settings → Field Groups → [Your Field Group]**
2. Edit the field you want to make translatable
3. Enable the "Translatable" option
4. Save the field configuration

### Translation Status

InspireCMS provides translation status indicators:

- **Fully Translated**: All translatable fields have content in this language
- **Partially Translated**: Some translatable fields have content in this language
- **Not Translated**: No content has been provided in this language

View translation status in the content list by enabling the language columns.

### Bulk Translation Management

For sites with extensive content:

1. Go to **Content → Translation Manager** (if available in your version)
2. Here you can:
   - See all content requiring translation
   - Filter by language or completion status
   - Export content for translation
   - Import translated content

## URL Structure for Multilingual Sites

InspireCMS supports different URL strategies for multilingual content:

### 1. Language Prefix URLs

```
/en/about-us
/fr/a-propos
/es/sobre-nosotros
```

This is the default and most common approach, adding the language code to the URL.

### 2. Domain-based Languages

```
en.yoursite.com/about-us
fr.yoursite.com/a-propos
es.yoursite.com/sobre-nosotros
```

For separate domains per language, configure in your `.env` or environment configuration:

```
INSPIRECMS_LANG_DOMAIN_MAPPING={"en":"en.yoursite.com","fr":"fr.yoursite.com","es":"es.yoursite.com"}
```

### 3. Custom URL Structure

For advanced URL handling, implement a custom segment provider:

```php
// config/inspirecms.php
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
        // Your custom logic to handle language in URLs
        // ...
    }
}
```

## Language Switching

### Adding a Language Switcher

InspireCMS provides helper functions to create language switchers:

```php
<!-- In your template -->
<div class="language-switcher">
    @foreach(inspirecms()->getAllAvailableLanguages() as $locale => $languageDto)
        <a 
            href="{{ request()->getLanguageUrl($locale) }}" 
            class="{{ app()->getLocale() === $locale ? 'active' : '' }}">
            {{ $languageDto->getLabel() }}
        </a>
    @endforeach
</div>
```

The `getLanguageUrl()` method automatically transforms the current URL to the equivalent in the selected language.

### Language Detection

InspireCMS can automatically detect a user's preferred language:

1. From URL parameters (explicit language choice)
2. From session data (previously selected language)
3. From browser preferences (Accept-Language header)
4. Defaulting to the site's default language

Configure language detection behavior:

```php
// config/inspirecms.php
'language_detection' => [
    'enabled' => true,
    'session_key' => 'inspirecms_locale',
    'cookie_key' => 'inspirecms_locale',
    'cookie_lifetime' => 60 * 24 * 30, // 30 days
    'methods' => ['url', 'session', 'cookie', 'browser', 'default'],
],
```

## Language-Specific Templates

For cases where different languages need different layouts:

### Using Language-Specific Components

```php
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

```php
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

## Translation Caching

InspireCMS caches translations for performance:

```php
// config/inspirecms.php
'cache' => [
    'languages' => [
        'key' => 'inspirecms.languages',
        'ttl' => 60 * 60 * 24, // 24 hours in seconds
    ],
],
```

Clear the language cache after making significant changes to language settings:

```bash
php artisan inspirecms:clear-language-cache
```

## Translation Fallbacks

When content is not available in the requested language:

1. InspireCMS looks for the content in the default language
2. If not found there, it searches for any available translation
3. Finally, it displays a "content not available" message if configured

Configure fallback behavior:

```php
// config/inspirecms.php
'language_fallback' => [
    'enabled' => true,
    'show_notice' => true, // Show notice that content is in fallback language
    'prefer_any_translation' => false, // If false, only falls back to default language
],
```

## Translation Services Integration

For larger sites with professional translation needs:

### Export for Translation

1. Go to **Content → Export**
2. Select "Translation Export" as the export type
3. Choose source and target languages
4. Select content to export
5. Download the export file (XLIFF or other format)

### Import Translations

1. Go to **Content → Import**
2. Select "Translation Import" as the import type
3. Upload the translated files
4. Review the changes
5. Confirm the import

## Best Practices

- **Start with Default Language**: Create content in your default language first
- **Consistent URLs**: Use consistent URL strategies across languages
- **Translation Workflow**: Establish a process for content translation
- **Language Variants**: Consider language variants (e.g., PT-BR vs. PT-PT) for targeted audiences
- **RTL Support**: Test thoroughly with right-to-left languages
- **Translation Status**: Regularly monitor translation status to ensure completeness
- **User Experience**: Make language switching obvious and consistent
- **SEO Optimization**: Use hreflang tags to help search engines understand your multilingual content