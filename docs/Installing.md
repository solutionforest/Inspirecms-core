# Installing

## Requirements

Before installing InspireCMS Core, ensure you have:

- **Laravel Application**: A working Laravel application
- **Laravel Herd**: [Laravel Herd](https://herd.laravel.com) or another suitable PHP development environment

## Installation

Access the admin panel at `/cms` (or your configured prefix) after installed.

### Via Composer
```bash
composer require inspirecms/core
```

### Run Install Command
- **With** sample data:
```bash
php artisan inspirecms:install
```

- **Without** sample data:
```bash
php artisan inspirecms:install --skip-samples
```

> [!Note] You can following optional command for xxx
> Import default data
> ```bash
> php artisan inspirecms:import-default-data
> ```
> Install required pacakges
> ```bash
> inspirecms:install-require-packages
> ```
> Publishing CMS panel
> ```bash
> inspirecms:publish-panel
> ```
> Repair permissions for CMS panel
> ```bash
> inspirecms:repair-permissions
> ```

### Start Schedule Jobs
Execute the schedule command to run scheduled jobs:
```bash
php artisan schedule:work
```

## Development Tips
1. Change the version in your `composer.json` to "dev":
   ```json
   {
       "require": {
           "inspirecms/core": "dev-main"
       },
       "minimum-stability": "dev"
   }
   ```

2. Run the following command to install the requested ckages:
   ```bash
   COMPOSER_ROOT_VERSION=dev-main composer update
   ```
3. Install composer dependencies
   ```bash
   composer install
   ```
4. Build assets
   ```bash
   npm i
   ```
   ```bash
    npm run build
   ```
5. Publishing assets
   ```bash
   php artisan filament:assets
   ```

