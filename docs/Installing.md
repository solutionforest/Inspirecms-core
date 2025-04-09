# Installing

## Requirements
Before installing InspireCMS Core, ensure you have:

- **PHP**: Version 8.3 or higher
- **Laravel Application**: A working Laravel application
- **Composer**: For package installation and dependency management
- **Database**: MySQL, PostgreSQL, or another Laravel-supported database
- **Node.js & NPM**: Required for building assets
- **Development Environment**: [Laravel Herd](https://herd.laravel.com), Laravel Sail, Valet, or another suitable PHP development environment
- **Command Line Access**: To run artisan commands


## Installation

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

### Optional Commands

> [!NOTE]
> You can run the following optional commands as needed:
> 
> Import default data:
> ```bash
> php artisan inspirecms:import-default-data
> ```
> 
> Install required packages:
> ```bash
> php artisan inspirecms:install-require-packages
> ```
> 
> Publish CMS panel:
> ```bash
> php artisan inspirecms:publish-panel
> ```
> 
> Repair permissions for CMS panel:
> ```bash
> php artisan inspirecms:repair-permissions
> ```

### Start Schedule Jobs
Execute the schedule command to run scheduled jobs:
```bash
php artisan schedule:work
```

Access the admin panel at `/cms` (or your configured prefix) after installed.

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

