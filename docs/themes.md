---
title: Themes
slug: themes
path: docs/v1/themes
uri: /docs/v1/themes
heading: Themes
brief:
quick_links: []
---

## Overview

Themes in InspireCMS are organized collections of templates, components, assets, and configurations that determine how your website looks and behaves. The theme system:

-   Separates content from presentation
-   Allows for easy switching between different designs
-   Enables component reuse across templates
-   Supports theme inheritance and overriding

---

## Theme Structure

A theme in InspireCMS consists of the following components:

```plaintext
resources/views/components/inspirecms/{theme-name}/
├── page.blade.php # Default page layout template
├── header.blade.php # Header component
├── footer.blade.php # Footer component
├── navigation.blade.php # Navigation component
└── ... other components
```

---

## Changing the Active Theme

To switch themes:

1. Go to **Settings** > **Templates**

    ![Setting_templates](https://inspirecms.net/storage/doc/ow4U79uhj4TvLoskWUv0lLWfI6iOZJwMmEtxtDOU.png)

2. Find the theme you want to activate
3. Click "Change theme" next to that theme
4. Confirm the change

The theme change takes effect immediately on your site.

> [!TIP]
> You can view the current theme by running `php artisan inspirecms:about`

## Creating a New Theme

### Using the Admin Panel

1. Go to **Settings** > **Templates**
2. Click "Create theme"
3. Enter a name for your new theme

    ![create_theme](https://inspirecms.net/storage/doc/H2GVIaqH6yJX3xarVebs2TExUG2YrEtWksDPVHFc.png)

4. Choose whether to base it on an existing theme
5. Submit a form

The new theme will be created in `resources/views/components/inspirecms/{your-theme-name}/`.

### Manual Creation

1. Create the theme directory structure:

```bash
mkdir -p resources/views/components/inspirecms/your-theme-name
```

2. Create the essential template files:

```bash
touch resources/views/components/inspirecms/your-theme-name/page.blade.php
```

3. Implement the basic templates:

```blade {title="resources/views/components/inspirecms/your-theme-name/page.blade.php"}
@php
    $locale ??= $content?->getLocale() ?? request()->getLocale();
    $title = $content?->getTitle();
    $seo = $content?->getSeo()?->getHtml();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale ?? app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    @if (isset($seo) && $seo instanceof \Illuminate\Contracts\Support\Htmlable)
        {{ $seo }}
    @endif
    <!-- Your CSS and other head elements -->
</head>
<body>
    <header>
        <!-- Header content -->
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer>
        <!-- Footer content -->
    </footer>
</body>
</html>
```

---

## Cloning a Theme

To create a new theme based on an existing one:

1. Go to **Settings** > **Templates**
2. Find the theme you want to clone
3. Click "Clone theme"

    ![select_theme](https://inspirecms.net/storage/doc/70XzMf3uMmb6QqfEiWMlNZhDW69yPcJ8YkDVDmVm.png)

4. Enter a name for the new theme
5. Click "Clone"

This copies all templates and components from the source theme to your new theme.

---

## Exporting Template Files

In InspireCMS, you can easily export your content templates to local storage. This allows you to customize your designs further, including building CSS with Tailwind. Follow these steps to export your templates:

1. Navigate to Admin Panel > **Settings** > **Templates**

2. **Export Content Templates**:
   Locate the "Export content templates" button in the Template info section. Click on it to export the blade files to your local storage.

    ![Export Content Templates](https://inspirecms.net/storage/doc/uxV0O3KHe35TU7Aa43u4Z427DehIWzpnd5IbaBdo.png)

3. **Build Your CSS**:
   Once the templates are exported, you can integrate them into your local environment. To build your CSS using Tailwind, run the following command in your terminal:
    ```bash
    npm run build
    ```

This will compile your Tailwind CSS files based on the exported templates, allowing you to customize your site's appearance effectively.

---

## Implementation Examples

More information reference on [Layout](./fe-layouts) {.doc-link}
