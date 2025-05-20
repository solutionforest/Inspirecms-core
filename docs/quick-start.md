---
title: Quick Start
slug: quick-start
path: docs/v1/quick-start
uri: /docs/1.x/quick-start
---
# Quick Start

This quick start guide will help you get up and running with InspireCMS in minutes.

---

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

---

## Creating Your First Content

1. Log in to the admin panel at `/cms`
2. Navigate to **Settings** > **Document Types**
3. Click "Create" to add a new document type (e.g., "Blog Post")
4. Add custom fields to your document type
5. Navigate to **Content** > **Pages**
6. Click "Create" to add new content using your document type

## Setting Up Your Frontend

1. Create a blade template in `resources/views/components/inspirecms/your-theme/page.blade.php`

2. Use the `@property` directive to access your content fields:

```blade
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

3. Navigate to **Settings** > **Document Types** > **Templates** to assign your template to content

```blade
<x-cms-template :content="$content" type="page">
// Adding content here
</x-cms-template>
```
