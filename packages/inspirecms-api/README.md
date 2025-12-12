# InspireCMS Headless API

A REST API package for InspireCMS that transforms it into a headless CMS. Automatically generate API endpoints based on your DocumentTypes.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Integrating with Filament Admin](#integrating-with-filament-admin)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [Query Parameters](#query-parameters)
- [Response Format](#response-format)
- [Error Handling](#error-handling)
- [Security](#security)
- [Testing](#testing)
- [Extending the API](#extending-the-api)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## Features

- **DocumentType-Driven API**: Automatically generate REST endpoints based on your content types
- **Field Exposure Control**: Configure which fields are visible in API responses
- **Flexible Authentication**: API tokens with abilities and expiration
- **Public/Private Access**: Configure read/write access per content type
- **Query Parameters**: Filtering, sorting, pagination, and field selection
- **Schema Discovery**: Auto-generated API documentation
- **Rate Limiting**: Built-in protection against API abuse
- **Multi-language Support**: Locale-aware content retrieval
- **Filament Integration**: Admin UI for managing API settings and tokens

## Requirements

- PHP >= 8.2
- Laravel >= 11.0
- InspireCMS Core >= 4.0
- Filament >= 3.3

## Installation

### Step 1: Add Repository

Add the package repository to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/inspirecms-api"
        }
    ]
}
```

### Step 2: Install Package

```bash
composer require solution-forest/inspirecms-api
```

### Step 3: Publish Configuration

```bash
php artisan vendor:publish --tag=inspirecms-api-config
```

### Step 4: Run Migrations

```bash
php artisan migrate
```

This creates the following tables:
- `cms_api_tokens` - Stores API authentication tokens
- Adds `api_settings` column to `cms_document_types`
- Adds `api_settings` column to `cms_fields`

### Step 5: Clear Cache

```bash
php artisan config:clear
php artisan route:clear
```

## Configuration

### Main Configuration File

After publishing, edit `config/inspirecms-api.php`:

```php
return [
    // Enable/disable the entire API
    'enabled' => env('INSPIRECMS_API_ENABLED', true),

    // URL prefix and version
    'prefix' => env('INSPIRECMS_API_PREFIX', 'api'),
    'version' => env('INSPIRECMS_API_VERSION', 'v1'),

    // Authentication settings
    'auth' => [
        'token_header' => 'Authorization',
        'api_key_header' => 'X-API-Key',
        'token_expiry_days' => 30,
    ],

    // Default settings for new DocumentTypes
    'defaults' => [
        'public_read' => false,
        'public_write' => false,
        'per_page' => 15,
        'max_per_page' => 100,
        'allowed_operations' => ['index', 'show'],
    ],

    // Rate limiting
    'rate_limiting' => [
        'enabled' => true,
        'public' => 60,        // requests per minute
        'authenticated' => 300, // requests per minute
    ],

    // Response caching
    'cache' => [
        'enabled' => true,
        'ttl' => 300,          // seconds
    ],
];
```

### Environment Variables

Add to your `.env` file:

```env
# Enable/Disable API
INSPIRECMS_API_ENABLED=true

# API URL Configuration
INSPIRECMS_API_PREFIX=api
INSPIRECMS_API_VERSION=v1

# Rate Limiting
INSPIRECMS_API_RATE_LIMIT_ENABLED=true
INSPIRECMS_API_RATE_LIMIT_PUBLIC=60
INSPIRECMS_API_RATE_LIMIT_AUTH=300

# Caching
INSPIRECMS_API_CACHE_ENABLED=true
INSPIRECMS_API_CACHE_TTL=300
```

## Integrating with Filament Admin

### Adding API Settings to DocumentType Resource

To add API configuration to your DocumentType form, modify your `DocumentTypeResource`:

```php
<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Schemas\Components\Tabs;
use SolutionForest\InspireCmsApi\Filament\Forms\Components\ApiSettingsForm;

class DocumentTypeResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Tabs')
                ->tabs([
                    // ... your existing tabs

                    Tabs\Tab::make('API')
                        ->icon('heroicon-o-code-bracket')
                        ->schema(ApiSettingsForm::getDocumentTypeSchema()),
                ]),
        ]);
    }

    // Handle data mutation before fill/save
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ApiSettingsForm::mutateFormDataBeforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ApiSettingsForm::mutateFormDataBeforeSave($data);
    }
}
```

### Adding API Settings to Field Resource

```php
<?php

use SolutionForest\InspireCmsApi\Filament\Forms\Components\ApiSettingsForm;

// In your Field form schema, add:
...ApiSettingsForm::getFieldSchema(),
```

### Registering API Token Resource

To manage API tokens in Filament, register the resource in your Panel Provider:

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use SolutionForest\InspireCmsApi\Filament\Resources\ApiTokenResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->resources([
                ApiTokenResource::class,
                // ... other resources
            ]);
    }
}
```

### Using the HasApiSettings Trait

Add the trait to your DocumentType model for helper methods:

```php
<?php

namespace App\Models;

use SolutionForest\InspireCms\Models\DocumentType as BaseDocumentType;
use SolutionForest\InspireCmsApi\Traits\HasApiSettings;

class DocumentType extends BaseDocumentType
{
    use HasApiSettings;
}
```

This provides methods like:
- `$documentType->isApiEnabled()`
- `$documentType->getApiSlug()`
- `$documentType->isApiPublicReadEnabled()`
- `$documentType->getAllowedApiOperations()`
- `$documentType->getApiEndpointUrl()`

## API Endpoints

### Schema Discovery

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/schema` | Get full API schema | No |
| GET | `/api/v1/schema/{type}` | Get schema for specific type | No |

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/auth/token` | Create API token | No (credentials) |
| DELETE | `/api/v1/auth/token` | Revoke current token | Yes |
| GET | `/api/v1/auth/tokens` | List user's tokens | Yes |
| DELETE | `/api/v1/auth/tokens/{id}` | Revoke specific token | Yes |

### Content (Dynamic per DocumentType)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/{type}` | List all items | Configurable |
| GET | `/api/v1/{type}/{id}` | Get item by ID | Configurable |
| GET | `/api/v1/{type}/slug/{slug}` | Get item by slug | Configurable |
| POST | `/api/v1/{type}` | Create new item | Usually Yes |
| PUT | `/api/v1/{type}/{id}` | Update item | Usually Yes |
| PATCH | `/api/v1/{type}/{id}` | Partial update | Usually Yes |
| DELETE | `/api/v1/{type}/{id}` | Delete item | Usually Yes |

## Authentication

### Creating a Token via API

```bash
curl -X POST https://yoursite.com/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "your-password",
    "name": "My App Token",
    "abilities": ["read", "write"],
    "expires_in_days": 30
  }'
```

**Response:**
```json
{
  "message": "Token created successfully.",
  "data": {
    "token": "abc123def456...",
    "type": "Bearer",
    "expires_at": "2024-02-15T00:00:00Z",
    "abilities": ["read", "write"]
  }
}
```

### Creating a Token via Admin Panel

1. Navigate to **Settings > API Tokens**
2. Click **Create Token**
3. Fill in:
   - Name (descriptive identifier)
   - Associated User (optional)
   - Abilities (permissions)
   - Expiration date (optional)
4. Save and copy the generated token

### Using Tokens

**Bearer Token (Recommended):**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://yoursite.com/api/v1/blog-posts
```

**API Key Header:**
```bash
curl -H "X-API-Key: YOUR_TOKEN" \
  https://yoursite.com/api/v1/blog-posts
```

**Query Parameter (Not recommended for production):**
```bash
curl "https://yoursite.com/api/v1/blog-posts?api_token=YOUR_TOKEN"
```

### Token Abilities

| Ability | Description |
|---------|-------------|
| `*` | Full access to all operations |
| `read` | GET requests only |
| `write` | POST, PUT, PATCH requests |
| `delete` | DELETE requests |

## Query Parameters

### Filtering

```bash
# Simple equality
GET /api/v1/blog-posts?filter[status]=published

# With operators
GET /api/v1/blog-posts?filter[created_at][gte]=2024-01-01
GET /api/v1/blog-posts?filter[created_at][lte]=2024-12-31

# Multiple values
GET /api/v1/blog-posts?filter[category_id][in]=1,2,3

# NULL checks
GET /api/v1/blog-posts?filter[deleted_at][null]=true

# LIKE search
GET /api/v1/blog-posts?filter[title][like]=Laravel
```

**Supported Operators:**

| Operator | SQL Equivalent | Example |
|----------|----------------|---------|
| `eq` | `=` | `filter[status][eq]=active` |
| `neq` | `!=` | `filter[status][neq]=draft` |
| `gt` | `>` | `filter[views][gt]=100` |
| `gte` | `>=` | `filter[created_at][gte]=2024-01-01` |
| `lt` | `<` | `filter[price][lt]=50` |
| `lte` | `<=` | `filter[price][lte]=100` |
| `like` | `LIKE` | `filter[title][like]=hello` |
| `in` | `IN` | `filter[id][in]=1,2,3` |
| `not_in` | `NOT IN` | `filter[id][not_in]=4,5` |
| `null` | `IS NULL` | `filter[deleted_at][null]=true` |
| `not_null` | `IS NOT NULL` | `filter[published_at][not_null]=true` |

### Sorting

```bash
# Single field ascending
GET /api/v1/blog-posts?sort=title

# Single field descending (prefix with -)
GET /api/v1/blog-posts?sort=-created_at

# Multiple fields
GET /api/v1/blog-posts?sort=-created_at,title
```

**Allowed Sort Fields:** `id`, `title`, `slug`, `created_at`, `updated_at`, `status`

### Pagination

```bash
# Basic pagination
GET /api/v1/blog-posts?page=2&per_page=20

# Maximum per_page is configurable (default: 100)
```

### Including Relations

```bash
# Single relation
GET /api/v1/blog-posts?include=author

# Multiple relations
GET /api/v1/blog-posts?include=author,children,parent
```

**Allowed Relations:** `documentType`, `parent`, `children`, `webSetting`, `author`, `latestVersion`, `templates`

### Locale/Language

```bash
# Get content in specific locale
GET /api/v1/blog-posts?locale=en

# Works with translatable fields
GET /api/v1/blog-posts/123?locale=fr
```

### Full-text Search

```bash
# Search in title and slug
GET /api/v1/blog-posts?search=laravel+tutorial
```

### Content Status

```bash
# Only published (default)
GET /api/v1/blog-posts?status=published

# Only drafts (requires auth)
GET /api/v1/blog-posts?status=draft

# All content (requires auth)
GET /api/v1/blog-posts?status=all
```

## Response Format

### Successful List Response

```json
{
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "type": "blog-posts",
      "attributes": {
        "title": "Getting Started with InspireCMS",
        "slug": "getting-started",
        "status": "published",
        "locale": "en",
        "content": "<p>Welcome to InspireCMS...</p>",
        "featured_image": {
          "id": 42,
          "url": "/storage/media/hero.jpg",
          "name": "hero.jpg",
          "mime_type": "image/jpeg",
          "alt": "Hero image"
        },
        "tags": ["cms", "laravel", "tutorial"]
      },
      "relationships": {
        "author": {
          "data": { "id": 1, "type": "users", "name": "John Doe" }
        },
        "document_type": {
          "data": { "id": 5, "type": "document_types", "name": "Blog Post" }
        }
      },
      "meta": {
        "created_at": "2024-01-15T10:00:00Z",
        "updated_at": "2024-01-15T12:00:00Z",
        "published_at": "2024-01-15T10:00:00Z"
      },
      "links": {
        "self": "/api/v1/blog-posts/550e8400-e29b-41d4-a716-446655440000"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 72
  },
  "links": {
    "first": "/api/v1/blog-posts?page=1",
    "last": "/api/v1/blog-posts?page=5",
    "prev": null,
    "next": "/api/v1/blog-posts?page=2"
  }
}
```

### Successful Single Response

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "type": "blog-posts",
    "attributes": {
      "title": "Getting Started with InspireCMS",
      "slug": "getting-started",
      "status": "published",
      "locale": "en",
      "content": "<p>Full content here...</p>"
    },
    "relationships": {},
    "meta": {
      "created_at": "2024-01-15T10:00:00Z",
      "updated_at": "2024-01-15T12:00:00Z",
      "published_at": "2024-01-15T10:00:00Z"
    },
    "links": {
      "self": "/api/v1/blog-posts/550e8400-e29b-41d4-a716-446655440000"
    }
  }
}
```

## Error Handling

### Error Response Format

```json
{
  "error": "Error Type",
  "message": "Human-readable error message"
}
```

### HTTP Status Codes

| Code | Meaning | Example |
|------|---------|---------|
| 200 | OK | Successful GET, PUT, PATCH |
| 201 | Created | Successful POST |
| 400 | Bad Request | Invalid request body |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Token lacks required ability |
| 404 | Not Found | Content or type not found |
| 405 | Method Not Allowed | Operation not enabled |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |
| 503 | Service Unavailable | API disabled |

### Common Error Responses

**401 Unauthorized:**
```json
{
  "error": "Unauthorized",
  "message": "No API token provided"
}
```

**404 Not Found:**
```json
{
  "error": "Not Found",
  "message": "Content type 'invalid-type' not found."
}
```

**429 Rate Limited:**
```json
{
  "error": "Too Many Requests",
  "message": "Rate limit exceeded. Try again in 60 seconds."
}
```

## Security

### Best Practices

1. **Always use HTTPS** in production
2. **Set appropriate token expiration** - 30 days is a good default
3. **Use specific abilities** instead of `*` when possible
4. **Enable rate limiting** to prevent abuse
5. **Keep public_write disabled** unless absolutely necessary
6. **Regularly audit and revoke** unused tokens
7. **Don't expose sensitive fields** in API responses

### CORS Configuration

The package includes CORS support. Configure in `config/inspirecms-api.php`:

```php
'cors' => [
    'enabled' => true,
    'allowed_origins' => ['https://your-frontend.com'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key'],
    'max_age' => 86400,
],
```

## Testing

### Running Package Tests

```bash
cd packages/inspirecms-api
composer install
./vendor/bin/pest
```

### Writing Tests for Your Application

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use SolutionForest\InspireCmsApi\Models\ApiToken;
use SolutionForest\InspireCms\Models\DocumentType;
use SolutionForest\InspireCms\Models\Content;

class ApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a document type with API enabled
        $this->documentType = DocumentType::create([
            'name' => 'Blog Posts',
            'slug' => 'blog-posts',
            'api_settings' => [
                'enabled' => true,
                'slug' => 'posts',
                'public_read' => true,
                'allowed_operations' => ['index', 'show'],
            ],
        ]);
    }

    public function test_can_list_content_via_api(): void
    {
        Content::factory()
            ->for($this->documentType)
            ->count(5)
            ->create();

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'attributes', 'meta', 'links']
                ],
                'meta',
                'links',
            ]);
    }

    public function test_authenticated_user_can_create_content(): void
    {
        $token = ApiToken::createToken('Test', null, ['write']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token['plain_token'])
            ->postJson('/api/v1/posts', [
                'title' => 'New Post',
                'slug' => 'new-post',
            ]);

        $response->assertStatus(201);
    }
}
```

## Extending the API

### Custom Field Transformers

```php
<?php

namespace App\Services;

use SolutionForest\InspireCmsApi\Services\FieldTransformerService;

class CustomFieldTransformer extends FieldTransformerService
{
    protected function transformFieldValue($field, $value, ?string $locale = null): mixed
    {
        // Handle custom field type
        if ($field->type === 'my_custom_type') {
            return $this->transformMyCustomType($value);
        }

        return parent::transformFieldValue($field, $value, $locale);
    }

    protected function transformMyCustomType($value): array
    {
        // Custom transformation logic
        return ['custom' => $value];
    }
}
```

Register in a service provider:

```php
$this->app->singleton(FieldTransformerService::class, CustomFieldTransformer::class);
```

### Custom Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;

class CustomApiMiddleware
{
    public function handle($request, Closure $next)
    {
        // Custom logic before API request
        $response = $next($request);
        // Custom logic after API request
        return $response;
    }
}
```

Register in route file or service provider.

### Adding Custom Endpoints

```php
// routes/api.php
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'inspirecms.api.enabled'])->group(function () {
    Route::get('custom/endpoint', [CustomController::class, 'index']);
});
```

## Troubleshooting

### Common Issues

**Q: API returns 404 for all routes**
A: Ensure the package is enabled (`INSPIRECMS_API_ENABLED=true`) and run `php artisan route:clear`.

**Q: Document type not appearing in API**
A: Check that `api_settings.enabled` is `true` for the document type.

**Q: Getting "Token has expired" error**
A: Create a new token or set `token_expiry_days` to `null` for non-expiring tokens.

**Q: Rate limiting is too restrictive**
A: Increase limits in config or set `rate_limiting.enabled` to `false` for development.

**Q: Fields not appearing in response**
A: Verify field's `api_settings.exposed` is `true`.

### Debug Mode

Enable debug logging:

```php
// config/inspirecms-api.php
'debug' => env('INSPIRECMS_API_DEBUG', false),
```

### Getting Help

- Check the [GitHub Issues](https://github.com/solutionforest/inspirecms-api/issues)
- Review InspireCMS documentation
- Contact support at info@solutionforest.net

## License

MIT License - see [LICENSE](LICENSE) file for details.

---

Made with ❤️ by [Solution Forest](https://solutionforest.net)
