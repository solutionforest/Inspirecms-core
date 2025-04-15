# Installing InspireCMS

This guide provides detailed instructions for installing InspireCMS in various environments.

## Prerequisites

Before beginning installation, ensure your environment meets the [system requirements](./Requirements.md).

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

```env
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

### Step 4: Run the Installer
The installer will set up the database, publish assets, and configure InspireCMS:

```bash
php artisan inspirecms:install
```

To skip sample data, use the --skip-samples flag:

```bash
php artisan inspirecms:install --skip-samples
```

### Step 5: Access the Admin Panel
After installation, you can access the admin panel at `/cms` (or your configured prefix). Use the credentials you provided during installation or the default admin user:

* Username: `admin@example.com`
* Password: `password`


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


## Docker Installation

InspireCMS can be installed in a Docker environment using Laravel Sail:

1. Install Laravel with Sail:

```bash
curl -s "https://laravel.build/my-inspirecms-project" | bash
cd my-inspirecms-project
```

2. Start Docker containers:

```bash
./vendor/bin/sail up -d
```

3. Install InspireCMS using Sail:

```bash
./vendor/bin/sail composer require solution-forest/inspirecms-core
./vendor/bin/sail artisan inspirecms:install
```


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

## Next Steps

After installing InspireCMS, you might want to:

* [Configure your settings](./Configuration.md)
* [Create document types](../core/Document.md)
* [Set up custom fields](../core/CustomFields.md)
* [Configure themes](../core/Themes.md)