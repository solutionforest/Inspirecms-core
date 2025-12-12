<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the entire API functionality. When disabled, all API
    | endpoints will return 404 responses.
    |
    */
    'enabled' => env('INSPIRECMS_API_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API Prefix & Version
    |--------------------------------------------------------------------------
    |
    | The URL prefix and version for all API endpoints.
    | Example: /api/v1/blog-posts
    |
    */
    'prefix' => env('INSPIRECMS_API_PREFIX', 'api'),
    'version' => env('INSPIRECMS_API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    |
    | Configure how API authentication works.
    |
    */
    'auth' => [
        // Header name for Bearer token authentication
        'token_header' => 'Authorization',

        // Header name for API key authentication (alternative method)
        'api_key_header' => 'X-API-Key',

        // Number of days before a token expires (null = never expires)
        'token_expiry_days' => 30,

        // Hash algorithm for API tokens
        'token_hash_algo' => 'sha256',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings for Document Types
    |--------------------------------------------------------------------------
    |
    | Default API settings applied when a DocumentType doesn't have
    | explicit API configuration.
    |
    */
    'defaults' => [
        // Whether content is publicly readable by default
        'public_read' => false,

        // Whether content can be created/updated publicly (usually false)
        'public_write' => false,

        // Default items per page
        'per_page' => 15,

        // Maximum items per page (prevents abuse)
        'max_per_page' => 100,

        // Default allowed operations for new document types
        'allowed_operations' => ['index', 'show'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Protect your API from abuse with rate limiting.
    |
    */
    'rate_limiting' => [
        'enabled' => env('INSPIRECMS_API_RATE_LIMIT_ENABLED', true),

        // Requests per minute for public/unauthenticated requests
        'public' => env('INSPIRECMS_API_RATE_LIMIT_PUBLIC', 60),

        // Requests per minute for authenticated requests
        'authenticated' => env('INSPIRECMS_API_RATE_LIMIT_AUTH', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Caching
    |--------------------------------------------------------------------------
    |
    | Cache API responses for improved performance.
    |
    */
    'cache' => [
        'enabled' => env('INSPIRECMS_API_CACHE_ENABLED', true),

        // Cache TTL in seconds (default: 5 minutes)
        'ttl' => env('INSPIRECMS_API_CACHE_TTL', 300),

        // Cache store to use (null = default)
        'store' => null,

        // Cache key prefix
        'prefix' => 'inspirecms_api',
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS Settings
    |--------------------------------------------------------------------------
    |
    | Cross-Origin Resource Sharing settings for the API.
    |
    */
    'cors' => [
        'enabled' => env('INSPIRECMS_API_CORS_ENABLED', true),

        // Allowed origins (* for all, or array of domains)
        'allowed_origins' => ['*'],

        // Allowed HTTP methods
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

        // Allowed headers
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key', 'Accept'],

        // Max age for preflight requests (in seconds)
        'max_age' => 86400,
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Model class mappings for the API package.
    |
    */
    'models' => [
        'api_token' => \SolutionForest\InspireCmsApi\Models\ApiToken::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the database table names used by the API package.
    |
    */
    'tables' => [
        'api_tokens' => 'cms_api_tokens',
    ],

];
