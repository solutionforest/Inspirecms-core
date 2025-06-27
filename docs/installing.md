---
title: Installing
slug: installing
path: docs/v1/installing
uri: /docs/v1/installing
heading: Installing
brief:
quick_links: []
---

## Prerequisites

Before beginning installation, ensure your environment meets the [system requirements](./requirements){.doc-link}.

---

## Standard Installation

### Step 1: Create a Laravel Application

You can install InspireCMS on a new Laravel application or an existing one:

```bash
# Create a new Laravel application
composer create-project laravel/laravel my-inspirecms-project
cd my-inspirecms-project
```

### Step 2: Configure Your Database

Update your `.env` file with your database credentials:

```ini {title=".env"}
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### Step 3: Install InspireCMS

Add InspireCMS to your project:

```bash
composer require solution-forest/inspirecms-core
```

### Step 4: Run the Install Command

The installer will set up the database, publish assets, and configure InspireCMS:

```bash
php artisan inspirecms:install
```

### Step 5: Access the Admin Panel

After installation, you can access the admin panel at `/cms` (or your configured path) and create your first admin user.

### Step 6: Remove Default Welcome Route (New Projects Only)

If you're working with a new Laravel application, remove the default welcome route from `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

// Remove or comment out this line:
// Route::get('/', function () {
//     return view('welcome');
// });

\SolutionForest\InspireCms\Facades\InspireCms::routes();

```

This allows InspireCMS to handle your site's routing properly.

### Step 7: Set Up Queue Workers and Scheduled Jobs

InspireCMS requires queue workers and scheduled jobs for image conversion, background processing, and other essential operations.

For development, you can run these commands manually in separate terminals:

-   `php artisan queue:work` (for background jobs)
-   `php artisan schedule:work` (for scheduled tasks)

For detailed configuration options, refer to the [Laravel Queue documentation](https://laravel.com/docs/queues) and [Task Scheduling documentation](https://laravel.com/docs/scheduling).

---

## Manual Installation Steps

If you need more control over the installation process, follow these steps:

1. Install required packages:

```bash
php artisan inspirecms:install-require-packages
```

2. Publish configuration, migrations, and assets:

```bash
php artisan vendor:publish --tag="inspirecms-config"
php artisan vendor:publish --tag="inspirecms-migrations"
```

3. Run migrations:

```bash
php artisan migrate
```

4. Publish panel:

```bash
php artisan inspirecms:publish-panel
```

5. Import default data:

```bash
php artisan inspirecms:import-default-data
```

6. Repair permissions:

```bash
php artisan inspirecms:repair-permissions
```

---

## Creating Your First Content

1. Log in to the admin panel at `/cms`
2. Navigate to **Settings** > **Document Types**
3. Click "Create" to add a new document type (e.g., "Blog Post")
4. Add custom fields to your document type
5. Navigate to **Content** > **Pages**
6. Click "Create" to add new content using your document type

For detailed information about creating and configuring document types, see the [Document Types documentation](./document-type){.doc-link}.

---

## Setting Up Your Frontend

1. Create a blade template in `resources/views/components/inspirecms/your-theme/page.blade.php`

2. Use the `@property` directive to access your content fields:

```blade
@props(['content', 'locale' => null])
@aware(['isPreviewing'])
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
@props(['isPeekPreviewModal' => false])
@php
    $locale ??= $content->getLocale();
@endphp
<x-cms-template type="page" :content="$content" :locale="$locale" :isPreviewing="$isPeekPreviewModal">
// Adding content here
</x-cms-template>
```

---

## Troubleshooting Common Issues

### Permissions Issues

If you encounter permission issues, ensure your web server has appropriate access:

```bash
# For Ubuntu/Debian
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### Database Connection Issues

If you're having trouble connecting to your database, verify your .env configuration and ensure the database exists.

### Package Discovery Problems

If Laravel isn't discovering the package, try clearing your cache:

```bash
php artisan config:clear
php artisan cache:clear
```
