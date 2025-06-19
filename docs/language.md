---
title: Language
slug: language
path: docs/v1/language
uri: /docs/1.x/language
heading: Language
brief: This guide covers language configuration, content translation, and multilingual site setup.
---

## Overview

The language system in InspireCMS allows you to:

-   Define multiple languages for your site
-   Translate content into different languages
-   Manage language-specific URLs

---

## Configuring Languages

### Managing Languages

Languages are managed through the admin panel:

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

-   A user first visits your site without a language preference
-   A requested content translation doesn't exist
-   Fallback content is required

To change the default language:

1. Go to **Settings** > **Languages**
2. Find the language you want to make default
3. Toggle the "Default" checkbox or edit and check "Default"

---

## Translating Content

InspireCMS manages content translations through a flexible system based on locale keys.

### Setting Up Translatable Fields

Configuring fields through the admin panel:

1. Go to **Settings** > **Custom Fields** > **[Your Field Group]**
2. Edit the field you want to make translatable
3. Enable the "Translatable" option
4. Save the field configuration

### Creating Multilingual Content

When creating or editing content:

1. Look for the language selector (often near the top of the form)
2. Select the language you want to create/edit content for
3. Enter content in that language
4. Switch to another language to provide translations
5. Fields that are translatable will show for each language

---

## URL Structure for Multilingual Sites

InspireCMS supports different URL strategies for multilingual content:

### Language Prefix URLs

```plaintext
/en/about-us
/fr/a-propos
/es/sobre-nosotros
```

This is the default and most common approach, adding the language code to the URL.

---

## Language Switching

### Adding a Language Switcher

InspireCMS provides helper functions to create language switchers:

```blade
<!-- In your template -->
<div class="language-switcher">
    @foreach(inspirecms()->getAllAvailableLanguages() as $locale => $languageDto)
        <a href="{{ url("/$locale") }}>{{ $languageDto->getLabel() }}</a>
    @endforeach
</div>
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

-   **Start with Default Language**: Create content in your default language first
-   **Consistent URLs**: Use consistent URL strategies across languages
-   **Language Variants**: Consider language variants (e.g., PT-BR vs. PT-PT) for targeted audiences
