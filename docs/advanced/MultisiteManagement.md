# Multisite Management

InspireCMS provides capabilities to manage multiple websites from a single installation. This guide explains how to set up and manage multiple sites within your InspireCMS application.

## Overview

Multisite functionality in InspireCMS allows you to:

- Run multiple websites from a single codebase
- Share content between sites or keep it separate
- Apply different themes to each site
- Configure site-specific settings
- Manage domain mapping for each site

## Setting Up Multisite

### Basic Configuration

To enable multisite functionality:

```php
// config/inspirecms.php
'multisite' => [
    'enabled' => true,
    'default_site' => 'primary', // The key of your default site
    'sites' => [
        'primary' => [
            'name' => 'Primary Site',
            'domains' => ['example.com', 'www.example.com'],
            'theme' => 'default',
            'locale' => 'en',
        ],
        'secondary' => [
            'name' => 'Secondary Site',
            'domains' => ['second-site.com', 'www.second-site.com'],
            'theme' => 'alternate',
            'locale' => 'es',
        ],
    ],
],
```

### Database Configuration

InspireCMS uses a single database for all sites by default, differentiating content through site association:

```php
// InspireCMS automatically adds a site_id column to relevant tables
// No additional database configuration is typically needed
```

If you prefer separate databases for each site, you can configure this in your `config/database.php` file:

```php
// config/database.php
'connections' => [
    'mysql_primary' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST_PRIMARY', '127.0.0.1'),
        'database' => env('DB_DATABASE_PRIMARY', 'forge'),
        // Other connection details...
    ],
    'mysql_secondary' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST_SECONDARY', '127.0.0.1'),
        'database' => env('DB_DATABASE_SECONDARY', 'forge'),
        // Other connection details...
    ],
],
```

And then map these connections in your InspireCMS configuration:

```php
// config/inspirecms.php
'multisite' => [
    // Other config...
    'sites' => [
        'primary' => [
            // Other settings...
            'database_connection' => 'mysql_primary',
        ],
        'secondary' => [
            // Other settings...
            'database_connection' => 'mysql_secondary',
        ],
    ],
],
```

## Managing Sites in the Admin Panel

### Site Selector

When multisite is enabled, a site selector appears in the admin panel, allowing you to switch between sites:

1. Log in to the admin panel
2. Use the site selector dropdown (typically in the top navigation bar) to switch between sites

### Site-Specific Settings

Each site can have its own settings:

1. Navigate to **Settings → Site Settings**
2. Ensure you're on the correct site using the site selector
3. Configure settings specific to the current site:
   - Site name and description
   - Default language
   - Theme selection
   - Domain settings

## Content Management in Multisite

### Creating Site-Specific Content

Content in InspireCMS can be site-specific or shared between sites:

```php
// Creating content for a specific site
$content = new \SolutionForest\InspireCms\Models\Content();
$content->title = 'Site-Specific Page';
$content->site_id = 'primary'; // Associate with a specific site
$content->save();

// Creating shared content (available on all sites)
$sharedContent = new \SolutionForest\InspireCms\Models\Content();
$sharedContent->title = 'Shared Page';
$sharedContent->is_shared = true; // Mark as shared
$sharedContent->save();
```

### Retrieving Site-Specific Content

The content API automatically filters content based on the current site:

```php
// Get content for the current site
$currentSiteContent = inspirecms_content()->getByDocumentType('page');

// Force getting content from a specific site
$specificSiteContent = inspirecms_content()
    ->forSite('secondary')
    ->getByDocumentType('page');

// Get shared content (available across all sites)
$sharedContent = inspirecms_content()
    ->shared()
    ->getByDocumentType('page');
```

## Theme Management

Each site can use a different theme:

```php
// config/inspirecms.php
'multisite' => [
    'sites' => [
        'primary' => [
            // Other settings...
            'theme' => 'corporate',
        ],
        'secondary' => [
            // Other settings...
            'theme' => 'blog',
        ],
    ],
],
```

Access the current site's theme in your code:

```php
// Get current site
$currentSite = inspirecms_site()->current();

// Get current site's theme
$theme = $currentSite->getTheme();

// Use the theme in your templates
$layoutComponent = inspirecms_templates()->getComponentWithTheme('layout', $theme);
```

## Domain Mapping

Configure domains for each site:

```php
// config/inspirecms.php
'multisite' => [
    'sites' => [
        'primary' => [
            'domains' => [
                'example.com',
                'www.example.com',
                'example.local'  // Local development
            ],
            // Other settings...
        ],
        'secondary' => [
            'domains' => [
                'second-site.com',
                'www.second-site.com',
                'second.local'  // Local development
            ],
            // Other settings...
        ],
    ],
],
```

InspireCMS automatically detects which site to serve based on the requested domain.

### Middleware for Site Detection

InspireCMS uses middleware to detect the current site:

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // Other middleware...
        \SolutionForest\InspireCms\Http\Middleware\DetectSite::class,
    ],
];
```

You can customize site detection by extending this middleware:

```php
namespace App\Http\Middleware;

use Closure;
use SolutionForest\InspireCms\Http\Middleware\DetectSite as BaseDetectSite;

class CustomSiteDetection extends BaseDetectSite
{
    public function handle($request, Closure $next)
    {
        // Custom site detection logic
        // For example, detect by URL path segment instead of domain
        $siteKey = $request->segment(1);
        if ($this->isSiteKey($siteKey)) {
            $this->setSite($siteKey);
            // Remove the site segment from the URL
            $request->setPathInfo(preg_replace("#^/{$siteKey}#", '', $request->getPathInfo()));
            return $next($request);
        }
        
        // Fall back to standard domain-based detection
        return parent::handle($request, $next);
    }
    
    protected function isSiteKey($key)
    {
        // Check if the key exists in your site configuration
        return array_key_exists($key, config('inspirecms.multisite.sites', []));
    }
}
```

## Assets and Media Management

Each site can have its own media library:

```php
// config/inspirecms.php
'multisite' => [
    'sites' => [
        'primary' => [
            // Other settings...
            'media' => [
                'disk' => 'public',
                'directory' => 'primary-site',
            ],
        ],
        'secondary' => [
            // Other settings...
            'media' => [
                'disk' => 'public',
                'directory' => 'secondary-site',
            ],
        ],
    ],
],
```

Access site-specific media:

```php
// Get media for the current site
$media = inspirecms_asset()->findByKeys($assetId);

// Get media for a specific site
$media = inspirecms_asset()->forSite('secondary')->findByKeys($assetId);
```

## Permissions and User Management

You can configure permissions for users across sites:

```php
// Give a user access to specific sites
$user = \SolutionForest\InspireCms\Models\User::find(1);
$user->assignToSite('primary');
$user->assignToSite('secondary');

// Check if a user has access to a site
if ($user->hasSite('primary')) {
    // User can access the primary site
}

// Remove a user from a site
$user->removeFromSite('secondary');

// Give a user admin access to all sites
$user->assignRole('super-admin');
```

## Navigation and URL Management

### Site-Specific Navigation

Each site can have its own navigation structure:

```php
// Get navigation for the current site
$navigation = inspirecms()->getNavigation('main');

// Get navigation for a specific site
$navigation = inspirecms()->getNavigation('main', null, 'secondary');
```

### URL Generation for Multisite

Generate URLs specific to each site:

```php
// Get a content URL for the current site
$url = $content->getUrl();

// Get a content URL for a specific site
$url = $content->getUrl(null, 'secondary');

// Get a URL with both language and site specified
$url = $content->getUrl('fr', 'secondary');
```

## Customizing Admin Panel for Multisite

You can customize the admin panel to better support your multisite setup:

```php
// app/Providers/AppServiceProvider.php
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Support\Site;

public function boot()
{
    // Add site filter to content listings
    \Filament\Facades\Filament::registerRenderHook(
        'filament.resources.content.table.before',
        fn () => view('partials.site-filter')
    );
    
    // Change admin panel logo/theme based on current site
    InspireCms::resolveCurrentSiteUsing(function () {
        $site = Site::detectFromRequest();
        
        // Change admin panel logo based on site
        config(['inspirecms.admin.brand.logo' => function () use ($site) {
            return view('logos.' . $site->getKey());
        }]);
        
        return $site;
    });
}
```

## Site-Specific Configuration and Environment

You can have site-specific environment configurations:

```php
// Create site-specific .env files
// .env.primary
// .env.secondary

// Load the correct environment in bootstrap/app.php
$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Detect site from domain and load the appropriate environment
$site = \SolutionForest\InspireCms\Support\Site::detectFromRequest();
if ($site) {
    $app->loadEnvironmentFrom('.env.' . $site->getKey());
}
```

## Best Practices

1. **Site Organization**: Plan your site structure carefully before implementation
2. **Content Sharing**: Decide which content should be shared and which should be site-specific
3. **Theme Design**: Build themes with multisite capability in mind
4. **Performance**: Consider caching strategies for multiple sites
5. **Testing**: Test each site thoroughly in isolation and together
6. **Permissions**: Implement clear permission structures for multisite management
7. **Documentation**: Document your multisite setup for future maintenance

## Troubleshooting

### Site Not Detected

If the wrong site is loading:

1. Check domain configuration in the site settings
2. Verify that the middleware for site detection is properly registered
3. Clear application cache: `php artisan cache:clear`
4. Check for conflicting route definitions

### Content Visibility Issues

If content isn't appearing on the expected site:

1. Verify the content's site association in the database
2. Check if the content should be shared across sites
3. Ensure proper permissions are set for viewing the content

### Cross-Site Asset Issues

If assets from one site appear on another:

1. Check that each site has its own media directory configured
2. Verify that URLs are being generated correctly for the current site
3. Clear the browser cache to ensure fresh assets are loaded