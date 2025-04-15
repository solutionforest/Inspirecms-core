# Upgrading InspireCMS

This guide will help you upgrade your InspireCMS installation to the latest version.

## Before Upgrading

Before upgrading, always:

1. **Back up your database**
2. **Back up your files**
3. **Review the [changelog](../../CHANGELOG.md)** for breaking changes

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

### Step 3: Update InspireCMS Assets

**Option A: Automatic Update (Recommended)**

```bash
php artisan inspirecms:update
```
This command handles:

* Publishing updated assets
* Running migrations
* Clearing caches
* Updating permissions

**Option B: Manual Update**

Alternatively, you can run each step manually:
```bash
# Publish updated assets
php artisan vendor:publish --tag="inspirecms-migrations" --force
php artisan vendor:publish --tag="inspirecms-translations" --force
php artisan vendor:publish --tag="inspirecms-support-migrations" --force
php artisan vendor:publish --tag="inspirecms-support-translations" --force
php artisan vendor:publish --tag="inspirecms-config" --force

# Run migrations
php artisan migrate

# Update permissions
php artisan inspirecms:repair-permissions
```

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
php artisan filament:assets
npm run build
```

### Still Having Issues?
If you continue to experience problems after upgrading, please:

1. Check the GitHub issues
2. Review the changelog for breaking changes
3. Contact support

