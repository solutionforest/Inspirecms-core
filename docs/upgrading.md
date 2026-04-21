---
title: Upgrading
slug: upgrading
path: docs/v1/upgrading
uri: /docs/v1/upgrading
heading: Upgrading
brief:
quick_links: []
---

## Before Upgrading

Before upgrading, always:

1. **Back up your database**
2. **Back up your files**

---

## Standard Upgrade Process

### Step 1: Update via Composer

Update InspireCMS core and dependencies:

```bash
composer update solution-forest/inspirecms-core
```

To update to a specific version:

```bash
composer require solution-forest/inspirecms-core:^1.0.0
```

### Step 2: Clear All Caches

Clear Laravel's caches to ensure changes take effect:

```bash
php artisan optimize:clear
```

### Step 3: Update InspireCMS

**Option A: Automatic Update (Recommended)**

```bash
php artisan inspirecms:update
```

This command handles:

- Running migrations
- Clearing caches
- Publishing cms panel
- Updating permissions

**Option B: Manual Update**

Alternatively, you can run each step manually:

- Publish updated assets

```bash
php artisan vendor:publish --tag="inspirecms-migrations" --force
php artisan vendor:publish --tag="inspirecms-translations" --force
php artisan vendor:publish --tag="inspirecms-support-migrations" --force
php artisan vendor:publish --tag="inspirecms-support-translations" --force
php artisan vendor:publish --tag="inspirecms-config" --force
```

- Run migrations

```bash
php artisan migrate
```

- Update permissions

```bash
php artisan inspirecms:repair-permissions
```

- Import cms default data, e.g. Language

```bash
php artisan inspirecms:import-default-data
```

---

## Laravel 12 and 13 Compatibility

InspireCMS v4 supports both Laravel 12 and Laravel 13.

### What changed

To avoid Laravel 13 model boot recursion errors (for example: `bootIfNotBooted` called while a model is still booting), model observer registration was centralized in service providers.

Observer registration now happens in:

- `InspireCmsServiceProvider::registerModelObservers()` (core models)
- `InspireCmsSupportServiceProvider::registerModelObservers()` (support models)

### If you override model classes

If you override model classes via InspireCMS config/model manifest, keep observer registration in the provider layer. Avoid registering observers inside model `boot()`, `booted()`, or trait boot methods for the same model.

Example migration pattern:

Before (model-level registration):

```php
protected static function booted()
{
	static::observe(ContentObserver::class);
}
```

After (provider-level registration):

```php
protected function registerModelObservers(): void
{
	InspireCmsConfig::getContentModelClass()::observe(ContentObserver::class);
}
```

If your custom model uses traits that previously registered observers in trait boot methods, move those observer registrations to the same provider method as well.

### Recommended dependency constraints

Use composer constraints that allow both versions:

```json
"laravel/framework": "^12.0|^13.0"
```

### Recommended CI matrix

Run your package test suite against both Laravel versions, ideally with both lowest and stable dependency sets, to catch compatibility regressions early.

---

## Troubleshooting Upgrades

### Configuration Issues

If your configuration appears outdated after upgrade:

```bash
php artisan vendor:publish --tag="inspirecms-config" --force
```

Then manually merge your customizations from the old config file.

### Database Schema Issues

If you encounter database schema issues:

```bash
php artisan migrate:status
```

### Asset Issues

If the admin panel appears broken after upgrade:

```bash
php artisan optimize:clear
php artisan filament:assets
```
