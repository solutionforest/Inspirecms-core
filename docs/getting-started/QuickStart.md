# Quick Start Guide

This quick start guide will help you get up and running with InspireCMS in minutes.

## Installation

1. Create a new Laravel application:

```bash
composer create-project laravel/laravel my-inspirecms-project
cd my-inspirecms-project
```

2. Install InspireCMS via Composer:

```bash
composer require solution-forest/inspirecms-core
```

3. Run the InspireCMS installer:

```bash
php artisan inspirecms:install
```

4. Access your admin panel at `/cms` and complete the setup wizard.

## Creating Your First Content

1. Log in to the admin panel at `/cms`
2. Navigate to "Content" > "Document Types"
3. Click "Create" to add a new document type (e.g., "Blog Post")
4. Add custom fields to your document type
5. Navigate to "Content" > "Pages"
6. Click "Create" to add new content using your document type

## Setting Up Your Frontend

1. Create a blade template in `resources/views/components/inspirecms/your-theme/page.blade.php`

2. Use the `@property` directive to access your content fields:

```php
<html>
    <head>
        <title>{{ $content->getTitle() }}</title>
    </head>
    <body>
        <h1>@property('hero', 'title')</h1>
        <div class="content">
            @property('content', 'body')
        </div>
    </body>
</html>
```

3. Navigate to "Settings" > "Templates" to assign your template to content

## Next Steps

Now that you have InspireCMS up and running, explore the following resources:

* [Directory Structure](../core/DirectoryStructure.md)
* [Custom Fields](../core/CustomFields.md)
* [Themes](../core/Themes.md)
* [Full Installation Guide](./Installing.md)