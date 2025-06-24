---
title: Installing
slug: installing
path: docs/v1/installing
uri: /docs/1.x/installing
heading: Installing
brief:
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

After installation, you can access the admin panel at `/cms` (or your configured prefix). Use the credentials you provided during installation or the default admin user:

-   Username: `admin@example.com`
-   Password: `password`

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
