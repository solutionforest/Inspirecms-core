# Updating InspireCMS Core

Follow these steps to update InspireCMS Core to the latest version:

## 1. Update via Composer

```bash
composer update inspirecms/core
```

## 2. Clear Application Cache

```bash
php artisan optimize:clear
```

> [!Note]
> You may need to remove `config/inspirecms.php` to ensure the update completes successfully.

## 3. Update InspireCMS Assets

### Option A: Automatic Update (Recommended)

```bash
php artisan inspirecms:update
```

### Option B: Manual Update

Run the following commands to publish all required assets:

```bash
php artisan vendor:publish -tag="inspirecms-migrations" --force
php artisan vendor:publish -tag="inspirecms-translations" --force
php artisan vendor:publish -tag="inspirecms-support-migrations" --force
php artisan vendor:publish -tag="inspirecms-support-translations" --force
php artisan vendor:publish -tag="inspirecms-config" --force
```

## 4. Run Database Migrations

```bash
php artisan migrate
```
