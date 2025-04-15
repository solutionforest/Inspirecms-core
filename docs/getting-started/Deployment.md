# Deployment Guide

This guide covers the recommended practices for deploying InspireCMS to production environments.

## Preparing for Deployment

### 1. Optimize Composer Dependencies { .font-bold  .text-2xl .my-2 }

Before deployment, optimize Composer's autoloader to improve performance:

```bash
composer install --no-dev --optimize-autoloader
```

### 2. Generate Application Key { .font-bold  .text-2xl .my-2 }

Ensure your application key is set:

```bash
php artisan key:generate
```

### 3. Configure Environment Variables { .font-bold  .text-2xl .my-2 }

Create a production `.env` file with appropriate settings:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_LOCALE=en

# Database settings
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Cache and session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# File system
FILESYSTEM_DISK=public

# InspireCMS specific
INSPIRECMS_LICENSE_KEY=your-license-key
```

### 4. Optimize Configuration and Routes { .font-bold  .text-2xl .my-2 }

Cache your configuration and routes for better performance:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Server Requirements

- PHP 8.3 or higher
- Nginx or Apache web server
- MySQL 5.7+ or PostgreSQL 10+
- Redis (recommended for caching)
- Composer
- Node.js and NPM (for asset compilation)
- SSL certificate (for secure connections)

## Web Server Configuration

### Nginx Configuration { .font-bold  .text-2xl .my-2 }

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name your-domain.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    root /path/to/your-project/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```

### Apache Configuration { .font-bold  .text-2xl .my-2 }

Ensure you have a proper `.htaccess` file in your public directory:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Database Migration and Seeding

Run migrations and seed your production database:

```bash
php artisan migrate --force
php artisan inspirecms:install-require-packages
php artisan inspirecms:publish-panel
php artisan inspirecms:import-default-data
```

## File Storage and Media

Configure proper permissions for storage directories:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Link your storage directory for public access:

```bash
php artisan storage:link
```

## Scheduled Tasks

Configure a cron job to run Laravel's scheduler:

```
* * * * * cd /path/to/your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Queue Workers

For better performance, run queue workers using a process monitor like Supervisor:

```
[program:inspirecms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your-project/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/your-project/storage/logs/worker.log
stopwaitsecs=3600
```

## Deployment Strategies

### Using Git { .font-bold  .text-2xl .my-2 }

1. Clone your repository on the server
2. Install dependencies: `composer install --no-dev --optimize-autoloader`
3. Update environment variables
4. Run migrations and optimizations
5. Restart queue workers and web server

### Using Deployer (PHP Deployment Tool) { .font-bold  .text-2xl .my-2 }

```php
// deploy.php
require 'recipe/laravel.php';

set('application', 'inspirecms');
set('repository', 'git@github.com:username/your-repo.git');
set('git_tty', true);
set('keep_releases', 5);

host('your-domain.com')
    ->user('deployer')
    ->set('deploy_path', '/var/www/your-project');

// Tasks
task('build', function () {
    run('cd {{release_path}} && npm ci && npm run build');
});

after('deploy:failed', 'deploy:unlock');
after('deploy:vendors', 'build');
after('deploy:vendors', 'artisan:storage:link');
```

## Post-Deployment Checks

1. Verify the CMS admin panel is accessible: `https://your-domain.com/cms`
2. Check frontend pages are loading properly
3. Test content creation and media uploads
4. Monitor error logs for any issues: `storage/logs/laravel.log`
5. Verify scheduled tasks are running as expected

## Monitoring and Maintenance

- Set up application monitoring with services like New Relic or Laravel Telescope
- Configure log rotation to prevent disk space issues
- Create regular database backups
- Monitor server resources (CPU, memory, disk space)
- Set up health checks for your application to detect issues quickly

## Scaling Considerations

- Use a CDN for static assets and media files
- Implement a caching layer with Redis
- Configure session storage to use Redis or a database
- Consider using load balancers for high-traffic sites
- Set up database replication if needed

## Zero-Downtime Deployment

For critical applications, implement zero-downtime deployment using:

- Blue-Green deployments
- Atomic deployments with symbolic links
- Rolling deployments across server clusters