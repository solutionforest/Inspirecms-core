---
title: Caching
slug: caching
path: docs/v1/caching
uri: /docs/v1/caching
heading: Caching
brief: 
quick_links: []
---


## Overview

Caching in InspireCMS accelerates content delivery by storing frequently accessed data in fast-access storage. The system employs several caching strategies:

- **Language Cache**
- **Navigation Cache**: Makes menu loading faster
- **Route Cache**: Improves URL resolution
- **KeyValue Cache**: Stores simple configuration and settings data

---

## Cache Configuration

Configure caching in your application's configuration files.

### Main Cache Settings

Configure basic cache settings in `config/inspirecms.php`:

```php {title="config/inspirecms.php"}
'cache' => [
    'languages' => [
        'key' => 'inspirecms.languages',
        'ttl' => 60 * 60 * 24, // 24 hours in seconds
    ],
    'navigation' => [
        'key' => 'inspirecms.navigation',
        'ttl' => 60 * 60 * 24,
    ],
    'content_routes' => [
        'key' => 'inspirecms.content_routes',
        'ttl' => 120 * 60 * 24, // 120 days in seconds
    ],
    'key_value' => [
        'ttl' => 60 * 60 * 24,
    ],
],
```

### Laravel Cache Driver Configuration

InspireCMS uses Laravel's caching system. Configure the cache driver in `config/cache.php`:

```php {title="config/cache.php"}
'default' => env('CACHE_DRIVER', 'file'),

'stores' => [
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache/data'),
    ],
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
    'memcached' => [
        'driver' => 'memcached',
        'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
        'sasl' => [
            env('MEMCACHED_USERNAME'),
            env('MEMCACHED_PASSWORD'),
        ],
        'options' => [
            // Memcached::OPT_CONNECT_TIMEOUT => 2000,
        ],
        'servers' => [
            [
                'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                'port' => env('MEMCACHED_PORT', 11211),
                'weight' => 100,
            ],
        ],
    ],
],
```

---

## Language Caching

### Working with Language Cache

Example of cached language retrieval:

```php
// This method internally uses caching
la = inspirecms()->getAllAvailableLanguages();
```

### Invalidating Language Cache

Clear the language cache manually:

```php
// Clear all language cache
\SolutionForest\InspireCms\Facades\InspireCms::forgetCachedLanguages();
```
---

## Navigation Caching

### Working with Navigation Cache

Example of cached navigation retrieval:

```php
// This method internally uses caching
$navigation = inspirecms()->getNavigation('main', 'en');
```

### Invalidating Navigation Cache

Clear the navigation cache manually:

```php
// Clear all navigation cache
\SolutionForest\InspireCms\Facades\InspireCms::forgetCachedNavigation();
```

---

## Route Caching

InspireCMS caches content routes for faster URL resolution.

### Route Cache Commands

In addition to Laravel's route caching, clear InspireCMS route cache:

```bash
php artisan route:clear
```

or

```bash
php artisan inspirecms:clear-cache --routes
```

---

## KeyValue Caching

InspireCMS provides a persistent key-value storage system with caching capabilities through the `KeyValue` model.


### Working with KeyValue Model

The `KeyValue` model provides a simple way to store and retrieve configuration values:

```php
<?php
// Find a value by key
$setting = \SolutionForest\InspireCms\Models\KeyValue::findKeyValue('site_maintenance_mode');

// Set a new key-value pair or update an existing one
\SolutionForest\InspireCms\Models\KeyValue::setKeyValue('max_upload_size', '20M');

```

### KeyValue Caching

KeyValue entries are automatically cached when accessed to improve performance:

```php
<?php
// This will retrieve from cache if available, or from database if not
$value = \SolutionForest\InspireCms\Facades\KeyValueCache::get('site_title');

// Remove a specific key from cache
\SolutionForest\InspireCms\Facades\KeyValueCache::forget('temp_announcement');

// Remove all cache
\SolutionForest\InspireCms\Facades\KeyValueCache::clear();
```

### Automatic Cache Invalidation

When KeyValue models are updated through the model, the cache is automatically invalidated through the KeyValueObserver.

---

## Cache Clearing

Properly clear cache when needed to ensure fresh content.

### Cache Clear Commands

InspireCMS provides specific commands for clearing various caches:

```bash
# Clear all InspireCMS caches
php artisan inspirecms:clear-cache --all

# Clear specific caches
php artisan inspirecms:clear-cache --languages
php artisan inspirecms:clear-cache --routes
php artisan inspirecms:clear-cache --navigation
```

### Automatic Cache Clearing

InspireCMS automatically clears relevant caches when:

- Content is created, updated or deleted
- Settings are changed
- Templates are modified
- Navigation is restructured
