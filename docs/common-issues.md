---
title: Common Issues
slug: common-issues
path: docs/v1/common-issues
uri: /docs/1.x/common-issues
---
# Common Issues

This page documents the most frequently encountered issues with InspireCMS and their solutions.

---

## Installation Issues

### Missing Dependencies

**Symptoms:** Installation fails with package requirement errors.

**Solution:**
```bash
# Make sure your composer.json has proper requirements
composer update --with-all-dependencies
# or install missing packages individually
composer require filament/filament:^3.0 spatie/laravel-permission:^6.0
```

### Database Migration Failures

**Symptoms:** You encounter errors during migration.

**Solution:**
```bash
# Completely wipe the database (removes all tables)
php artisan db:wipe

# Remove all migrations from the migrations table
# and reinstall InspireCMS with fresh database structure
php artisan inspirecms:install
```

### Cannot Access Admin Panel

**Symptoms:** 404 error or redirect loop when trying to access `/cms`.

**Solution:**
1. Verify the admin path in your configuration:

```php {title="config/inspirecms.php"}
'admin' => [
    'path' => 'cms', // Make sure this matches your expected URL
],
```

2. Clear configuration cache:

```bash
php artisan config:clear
php artisan cache:clear
```

3. Check CmsPanelProvider is registered in your providers:

```php {title="bootstrap/providers.php"}
return [
    // Other providers...
    SolutionForest\InspireCms\CmsPanelProvider::class,
];
```

---

## Content Management Issues

### Content Not Displaying on Frontend

**Symptoms:** Content is visible in admin panel but not on frontend.

**Solution:**
1. Check content publication status:
   - Ensure content is set to "Published" not "Draft"
   - Verify publication date is not in the future

2. Clear content route cache:
```bash
php artisan cache:clear
```

2. Verify template assignment:
   - Check if content has a template assigned
   - Ensure template files exist in correct location

### Cannot Upload Media

**Symptoms:** Media upload fails with errors.

**Solution:**
1. Check file permissions:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

2. Verify allowed file types and size limits:
```php {title="config/inspirecms.php"}
'media' => [
    'media_library' => [
        'allowed_mime_types' => [...], // Add your required mime types
        'max_file_size' => 10 * 1024, // Adjust size limit in KB
    ],
],
```

3. Check disk configuration:
```php {title="config/filesystems.php"}
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

---

## Template Issues

### Templates Not Rendering Correctly

**Symptoms:** Template appears broken or content fields don't display.

**Solution:**
1. Check for Blade syntax errors in your template files
2. Verify property directives are used correctly:

```blade
// Correct usage
@property('hero', 'title')

// Make sure field groups and field names match exactly
@if($content->hasProperty('hero', 'title'))
    <h1>{{ $hero_title }}</h1>
@endif
```

3. Check theme configuration:
```php {title="config/inspirecms.php"}
'template' => [
    'default_theme' => 'your-theme', // Make sure this matches your theme name
    // ...
],
```

### Theme Assets Not Loading

**Symptoms:** CSS, JS, or images from theme aren't loading.

**Solution:**
1. Run the publish command for assets:
```bash
php artisan vendor:publish --tag="inspirecms-assets"
```

2. Check file paths in your templates:
```blade
// Use the asset helper for public files
<link href="{{ asset('css/theme.css') }}" rel="stylesheet">

// Or for specific theme assets
<link href="{{ asset('themes/' . inspirecms_templates()->getCurrentTheme() . '/css/style.css') }}" rel="stylesheet">
```

---

## Permission Issues

### "Access Denied" Errors

**Symptoms:** Users cannot access certain areas despite being logged in.

**Solution:**
1. Check role assignments:
   - Verify user has appropriate role
   - Ensure role has required permissions

2. Repair permissions:
```bash
php artisan inspirecms:repair-permissions
```

3. Clear permission cache:
```bash
php artisan cache:clear
```

### Can't Create Admin User

**Symptoms:** No way to access admin after fresh installation.

**Solution:**
1. Create a new admin user:
```bash
# Create a user with Filament's built-in command
php artisan make:filament-user
```

2. Import default data to ensure admin role exists:
```bash
# Import default data including roles
php artisan inspirecms:import-default-data
```

3. Assign admin role to the user:
```bash
# You can do this through the admin panel if you can access it
# Or use the Laravel tinker shell:
php artisan tinker
>>> $user = \SolutionForest\InspireCms\Models\User::where('email', 'your-admin-email@example.com')->first();
>>> $user?->assignRole(\SolutionForest\InspireCms\Facades\PermissionManifes::getSuperAdminRoleName(), \SolutionForest\InspireCms\Helpers\AuthHelper::guardName());
>>> exit
```

---

## Advanced Issues

### Performance Problems

**Symptoms:** Slow admin panel or frontend.

**Solution:**
1. Enable caching:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. Check for heavy queries in logs
3. Consider adding indexes to frequently queried columns

### Custom Field Types Not Working

**Symptoms:** Custom fields don't save or display properly.

**Solution:**
1. Register custom field config:
```php {title="config/inspirecms.php"}
'custom_fields' => [
    'extra_config' => [
        // Add your custom field config class
        \App\Fields\Configs\YourCustomField::class,
    ],
],
```

2. Check field implementation against documentation
3. Clear cache after adding custom fields:
```bash
php artisan cache:clear
```

## Still Need Help?

If your issue isn't listed here or the solutions don't work, please:

- Check the [Debugging Guide](./debugging){.doc-link} for diagnostic steps