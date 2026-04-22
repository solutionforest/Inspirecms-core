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

To avoid Laravel 13 model boot recursion errors (for example: `bootIfNotBooted` called while a model is still booting), observer registration was changed from using `Model::observe()` to direct event listener registration within trait boot methods.

Event listeners are now registered directly in trait boot methods instead of using `Model::observe()`.

### If you use traits with observer patterns

When using traits that register observers, register event listeners directly in the trait's boot method instead of using `Model::observe()`.

Example migration pattern:

Before (using observe):

```php
public static function bootYourTrait()
{
	static::observe(YourObserver::class);
}
```

After (using event listeners):

```php
public static function bootYourTrait()
{
	static::created(fn ($model) => (new YourObserver)->created($model));
	static::updated(fn ($model) => (new YourObserver)->updated($model));
	static::deleted(fn ($model) => (new YourObserver)->deleted($model));
}
```

If your model has multiple trait-boot observers, register each trait's listeners separately in its own boot method.

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
