# InspireCMS Headless API

A REST API package for InspireCMS that transforms it into a headless CMS. Automatically generate API endpoints based on your DocumentTypes.

## Features

- **DocumentType-Driven API**: Automatically generate REST endpoints based on your content types
- **Field Exposure Control**: Configure which fields are visible in API responses
- **Flexible Authentication**: API tokens with abilities and expiration
- **Public/Private Access**: Configure read/write access per content type
- **Query Parameters**: Filtering, sorting, pagination, and field selection
- **Schema Discovery**: Auto-generated API documentation
- **Rate Limiting**: Built-in protection against API abuse

## Installation

1. Add the package to your composer.json:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/inspirecms-api"
        }
    ],
    "require": {
        "solution-forest/inspirecms-api": "*"
    }
}
```

2. Run composer update:

```bash
composer update
```

3. Publish the configuration:

```bash
php artisan vendor:publish --tag=inspirecms-api-config
```

4. Run migrations:

```bash
php artisan migrate
```

## Configuration

### Enable API for a DocumentType

1. Go to **Settings > Document Types** in the CMS admin
2. Edit a document type
3. Navigate to the **API** tab
4. Enable the API and configure settings

Or programmatically:

```php
$documentType->update([
    'api_settings' => [
        'enabled' => true,
        'slug' => 'blog-posts',
        'public_read' => true,
        'public_write' => false,
        'allowed_operations' => ['index', 'show'],
    ]
]);
```

### Configure Field Exposure

Each field can be configured to:
- Be exposed or hidden in API responses
- Be readable (included in GET responses)
- Be writable (accepted in POST/PUT requests)
- Have a custom alias name in the API

## API Endpoints

Once a DocumentType has API enabled, the following endpoints become available:

### Content Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/{type}` | List all items |
| GET | `/api/v1/{type}/{id}` | Get item by ID |
| GET | `/api/v1/{type}/slug/{slug}` | Get item by slug |
| POST | `/api/v1/{type}` | Create new item |
| PUT | `/api/v1/{type}/{id}` | Update item |
| DELETE | `/api/v1/{type}/{id}` | Delete item |

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/token` | Create API token |
| DELETE | `/api/v1/auth/token` | Revoke current token |
| GET | `/api/v1/auth/tokens` | List user's tokens |

### Schema Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/schema` | Get full API schema |
| GET | `/api/v1/schema/{type}` | Get schema for type |

## Query Parameters

### Filtering

```
GET /api/v1/blog-posts?filter[status]=published
GET /api/v1/blog-posts?filter[created_at][gte]=2024-01-01
GET /api/v1/blog-posts?filter[author_id][in]=1,2,3
```

Supported operators:
- `eq` - Equal (default)
- `neq` - Not equal
- `gt` / `gte` - Greater than (or equal)
- `lt` / `lte` - Less than (or equal)
- `like` - LIKE search
- `in` / `not_in` - IN / NOT IN
- `null` / `not_null` - NULL checks

### Sorting

```
GET /api/v1/blog-posts?sort=title           # Ascending
GET /api/v1/blog-posts?sort=-created_at     # Descending
GET /api/v1/blog-posts?sort=-created_at,title  # Multiple
```

### Pagination

```
GET /api/v1/blog-posts?page=2&per_page=20
```

### Including Relations

```
GET /api/v1/blog-posts?include=author,children
```

### Locale

```
GET /api/v1/blog-posts?locale=en
```

### Search

```
GET /api/v1/blog-posts?search=keyword
```

## Authentication

### Creating a Token

```bash
curl -X POST https://yoursite.com/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "your-password",
    "name": "My App Token"
  }'
```

Response:
```json
{
  "message": "Token created successfully.",
  "data": {
    "token": "abc123...",
    "type": "Bearer",
    "expires_at": "2024-02-15T00:00:00Z"
  }
}
```

### Using the Token

```bash
# Bearer token
curl -H "Authorization: Bearer abc123..." https://yoursite.com/api/v1/blog-posts

# API Key header
curl -H "X-API-Key: abc123..." https://yoursite.com/api/v1/blog-posts
```

## Response Format

### List Response

```json
{
  "data": [
    {
      "id": "uuid-1",
      "type": "blog-posts",
      "attributes": {
        "title": "My Blog Post",
        "slug": "my-blog-post",
        "status": "published",
        "locale": "en",
        "content": "<p>...</p>",
        "featured_image": {
          "id": 42,
          "url": "/storage/media/image.jpg"
        }
      },
      "meta": {
        "created_at": "2024-01-15T10:00:00Z",
        "updated_at": "2024-01-15T12:00:00Z",
        "published_at": "2024-01-15T10:00:00Z"
      },
      "links": {
        "self": "/api/v1/blog-posts/uuid-1"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  },
  "links": {
    "first": "/api/v1/blog-posts?page=1",
    "last": "/api/v1/blog-posts?page=5",
    "next": "/api/v1/blog-posts?page=2"
  }
}
```

## Environment Variables

```env
INSPIRECMS_API_ENABLED=true
INSPIRECMS_API_PREFIX=api
INSPIRECMS_API_VERSION=v1
INSPIRECMS_API_RATE_LIMIT_ENABLED=true
INSPIRECMS_API_RATE_LIMIT_PUBLIC=60
INSPIRECMS_API_RATE_LIMIT_AUTH=300
INSPIRECMS_API_CACHE_ENABLED=true
INSPIRECMS_API_CACHE_TTL=300
```

## Extending the API

### Custom Field Transformers

You can register custom transformers for field types:

```php
use SolutionForest\InspireCmsApi\Services\FieldTransformerService;

$transformer = app(FieldTransformerService::class);
// Add custom transformation logic
```

### Adding Middleware

```php
// In your service provider
Route::middleware(['your-middleware'])->group(function () {
    // Custom API routes
});
```

## License

MIT License - see LICENSE file for details.
