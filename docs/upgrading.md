---
title: Upgrading
slug: upgrading
path: docs/v1/upgrading
uri: /docs/1.x/upgrading
heading: Upgrading
brief:
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

-   Running migrations
-   Clearing caches
-   Publishing cms panel
-   Updating permissions

**Option B: Manual Update**

Alternatively, you can run each step manually:

-   Publish updated assets

```bash
php artisan vendor:publish --tag="inspirecms-migrations" --force
php artisan vendor:publish --tag="inspirecms-translations" --force
php artisan vendor:publish --tag="inspirecms-support-migrations" --force
php artisan vendor:publish --tag="inspirecms-support-translations" --force
php artisan vendor:publish --tag="inspirecms-config" --force
```

-   Run migrations

```bash
php artisan migrate
```

-   Update permissions

```bash
php artisan inspirecms:repair-permissions
```

-   Import cms default data, e.g. Language

```bash
php artisan inspirecms:import-default-data
```

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
