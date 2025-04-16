# Database Structure

InspireCMS uses a carefully designed database structure to store content, user data, and system settings efficiently. This guide explains the database architecture, key tables, relationships, and schema details to help developers understand and extend the system.

## Database Overview

InspireCMS uses a relational database structure with several interconnected tables to manage:

- Content and content structure
- Media and assets
- User accounts and permissions
- Template and field definitions
- Site configuration and settings
- Navigation and routing

## Table Naming Convention

Tables in InspireCMS follow a consistent naming convention:

- All tables are prefixed with `cms_` (configurable)
- Singular nouns for entity tables (e.g., `cms_content`)
- Snake case for multi-word table names (e.g., `cms_document_type`)
- Pivot tables use both entity names (e.g., `cms_content_tag`)

## Key Tables

### Content Management

#### `cms_contents`

Stores all content items in the system.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `parent_id` | UUID | Parent content ID (for hierarchy) |
| `document_type_id` | Integer | Reference to `document type` |
| `title` | JSON | Translatable content title |
| `slug` | String | URL-friendly identifier |
| `status` | Integer | Content status (0=draft, 1=published, etc.) |
| `is_default` | Boolean | Whether this is the default content |
| `author_id` | UUID | User who created the content |
| `author_type` | String | Morph type for `author_id` |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |
| `deleted_at` | Timestamp | Soft delete timestamp |

#### `cms_content_versions`

Stores version history for content items.
#### `cms_content_versions`

Stores version history for content items.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `created_at` | Timestamp | Creation timestamp |
| `event_name` | String | Type of event that triggered this version (create, update, publish, etc.) |
| `publish_state` | String | Publishing state of this version (draft, published, archived, etc.) |
| `content_id` | UUID | Reference to `content` |
| `from_data` | JSON | Previous state of content before changes were made |
| `to_data` | JSON | New state of content after changes were made |
| `avoid_to_clean` | Boolean | Flag that prevents automatic cleanup/deletion of this version |
| `author_id` | UUID | User who created the content version |
| `author_type` | String | Morph type for `author_id` |

#### `cms_content_publish_version`

Maps content items to their currently published version.

| Column | Type | Description |
|--------|------|-------------|
| `content_id` | UUID | Reference to `content` |
| `version_id` | id | Reference to `content version` |
| `published_at` | Timestamp | When this version was published |

#### `cms_content_paths`

Maps content to their hierarchical paths in the site structure.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `key` | UUID | Reference to `content` |
| `value` | String | Full path to content |

#### `cms_content_routes`

Defines URL routes to content.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `content_id` | UUID | Reference to `content` |
| `language_id` | Integer | Reference to `language` |
| `uri` | String | URL pattern |
| `is_default_pattern` | Boolean | Whether this is the default route pattern |
| `regex_constraints` | JSON | Route pattern constraints |

#### `cms_content_web_settings`

Stores web-specific settings for content items.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `content_id` | UUID | Reference to `content` |
| `seo` | JSON | Stores SEO metadata like title, description, and keywords |
| `rebots` | JSON | Controls search engine crawling and indexing rules |
| `redirect_path` | String | Custom URL path for content redirection |
| `redirect_content_id` | UUID | Reference to `content item` for internal redirection |
| `redirect_content_type` | Integer | HTTP redirect status code (301, 302, etc.) |

#### `cms_content_locks`

Manages content locking for concurrent editing.

| Column | Type | Description |
|--------|------|-------------|
| `content_id` | UUID | Reference to content |
| `owner_id` | UUID | User holding the lock |
| `owner_type` | String | Morph type for user |
| `locked_at` | Timestamp | When the lock was acquired |

#### `cms_document_types`

Defines the different types of content in the system.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `title` | String | Document type name/title |
| `slug` | String | URL-friendly identifier |
| `icon` | String | Icon identifier |
| `category` | String | Document type category |
| `show_as_table` | Boolean | Display mode in admin UI |
| `show_at_root` | Boolean | Whether to show at root level |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_document_type_inheritance`

Defines inheritance relationships between document types.

| Column | Type | Description |
|--------|------|-------------|
| `document_type_id` | Integer | Reference to the `document type` that inherits properties |
| `inherited_document_type_id` | Integer | Reference to the `document type` being inherited from |

#### `cms_document_type_allowed_document_type`

Defines which document types can be created as children of other document types.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Reference to the parent `document type` |
| `allowed_id` | Integer | Reference to `document type` that's allowed as a child |

### Field Management

#### Field Groups and Fields

These tables manage field groups and fields for content types. The actual table names depend on the configuration of the [filament-field-group package](https://github.com/solutionforest/filament-field-group) and may vary based on your installation settings. For detailed information about these tables, please refer to the package documentation.


#### `cms_field_groupables`

Polymorphic pivot table linking field groups to content types, templates, etc.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `field_group_id` | Integer | Reference to `field group` |
| `groupable_id` | UUID | Polymorphic ID |
| `groupable_type` | String | Polymorphic type |
| `inherited_from_id` | Integer | ID of the parent entity this field group inherits from |
| `inherited_from_type` | String | Type of entity this field group inherits from |

### Template Management

#### `cms_templates`

Stores template definitions.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `slug` | String | Unique URL-friendly identifier for the template |
| `content` | Text | Template content |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_templateables`

Polymorphic pivot table linking templates to content.

| Column | Type | Description |
|--------|------|-------------|
| `template_id` | UUID | Reference to `template` |
| `templateable_type` | String | Polymorphic type |
| `templateable_id` | UUID | Polymorphic ID |
| `is_default` | Boolean | Whether this template is the default for the associated content |

### User Management

#### `cms_users`

Stores user accounts.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `name` | String | User's full name |
| `email` | String | User's email address |
| `preferred_language` | String | User's preferred interface language |
| `avatar` | String | Path or reference to user's profile image |
| `password` | String | Hashed password |
| `remember_token` | String | "Remember me" token |
| `failed_login_attempt` | Integer | Count of consecutive failed login attempts |
| `last_lockouted_at` | Timestamp | When user account was last locked out |
| `last_password_change_date` | Timestamp | When user last changed their password |
| `last_logged_in_at` | Timestamp | When user last successfully logged in |
| `email_confirmed_at` | Timestamp | When user confirmed their email address |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_user_login_activities`

Tracks user login events and activity.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Interger | Primary key |
| `user_id` | UUID | Reference to `user` |
| `last_logged_in_at_utc` | Timestamp | When login attempt occurred |
| `last_logged_out_at_utc` | Timestamp | When user logged out (if tracked) |
| `ip_address` | String | Client IP address |

### User Roles and Permissions

InspireCMS uses the [spatie/laravel-permission](https://github.com/spatie/laravel-permission) package to manage user roles and permissions. The package provides tables for roles, permissions, and their relationships.

For detailed information about the database schema for roles and permissions, please refer to the [Laravel Permission package documentation](https://spatie.be/docs/laravel-permission).

### Media Management

#### `cms_media_assets`

Stores media assets.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `title` | String | The name/title of the media asset |
| `parent_id` | UUID | Reference to parent folder ID (for hierarchy) |
| `is_folder` | Boolean | Whether this record represents a folder rather than a file |
| `caption` | String | Optional descriptive text for the media asset |
| `author_id` | UUID | User who uploaded/created the media asset |
| `author_type` | String | Morph type for `author_id` |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

### Media Library

InspireCMS integrates with [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) for advanced media management, which provides:

- **Media Table**: Stores media items with polymorphic relationships
- **Collections**: Organizes media into named groups
- **Conversions**: Manages image transformations (thumbnails, responsive versions)
- **Custom Properties**: Allows metadata storage for media items

Key relationships:
- Media items link to CMS assets via UUID
- Polymorphic relationships connect media to various content types
- Collections group related media items

Media files are stored in the filesystem rather than the database, with conversions and responsive images generated automatically.

### Site Configuration

#### `cms_languages`

Defines available languages.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `code` | String | ISO language code (e.g., 'en', 'fr', 'es') |
| `is_default` | Boolean | Whether this is the default language |
| `is_active` | Boolean | Whether language is active |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_navigation`

Stores navigation menu structure using [nested set model](https://github.com/lazychaser/laravel-nestedset) from kalnoy/nestedset package.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `parent_id` | Integer | Parent navigation item ID (for hierarchy) |
| `_lft` | Integer | Left value for nested set pattern |
| `_rgt` | Integer | Right value for nested set pattern |
| `category` | String | Navigation category (main, footer, etc.) |
| `title` | JSON | Translatable title |
| `type` | String | Link type (content, link, group) |
| `url` | JSON | Translatable URL for external links |
| `content_id` | UUID | Reference to content (for content links) |
| `target` | String | Link target (_blank, _self, etc.) |
| `is_active` | Boolean | Whether this navigation item is active |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

The nested set implementation allows for efficient querying of hierarchical navigation structures, making it easy to retrieve entire navigation trees with a single query and maintain proper ordering.

#### `cms_key_values`

Stores key-value pairs for application settings.

| Column | Type | Description |
|--------|------|-------------|
| `key` | String | Primary key |
| `value` | JSON | Setting value |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_sitemaps`

Stores sitemap configuration for search engine optimization.

| Column | Type | Description |
|--------|------|-------------|
| `id` | Integer | Primary key |
| `model_id` | UUID | Polymorphic relationship ID (content, page, etc.) |
| `model_type` | String | Polymorphic model class name |
| `url` | String | URL path for the sitemap entry |
| `change_frequency` | String | How frequently the page changes (daily, weekly, monthly, etc.) |
| `priority` | Decimal | SEO priority value (0.0 to 1.0) |
| `enable` | Boolean | Whether to include in sitemap |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

## Database Relationships

Understanding the relationships between tables is crucial:

### Content Relationships

- **Content Hierarchy**: `cms_contents.parent_id` ↔ `cms_contents.id` (self-referencing)
- **Content Type**: `cms_contents.document_type_id` → `cms_document_types.id`
- **Content Path**: `cms_content_paths.content_id` → `cms_contents.id`
- **Content Route**: `cms_content_routes.content_id` → `cms_contents.id`
- **Content Language**: `cms_content_routes.language_id` → `cms_languages.id`
- **Content Creator**: `cms_contents.created_by` → `cms_users.id`
- **Content Editor**: `cms_contents.updated_by` → `cms_users.id`

### Field Relationships

- **Field Group Fields**: `cms_fields.field_group_id` → `cms_field_groups.id`
- **Document Type Field Groups**: Polymorphic through `cms_field_groupables`
- **Template Field Groups**: Polymorphic through `cms_field_groupables`

### Template Relationships

- **Template Document Type**: `cms_templates.document_type_id` → `cms_document_types.id`
- **Content Template**: Polymorphic through `cms_templateables`

### User Relationships

- **User Roles**: Polymorphic through `cms_model_has_roles`
- **Role Permissions**: Through `cms_role_has_permissions`

### Navigation Relationships

- **Navigation Hierarchy**: `cms_navigations.parent_id` ↔ `cms_navigations.id` (self-referencing)
- **Navigation Content**: `cms_navigations.content_id` → `cms_contents.id`

## Schema Configuration

The database schema is defined through migrations in the `database/migrations` directory. Key migrations include:

- `create_contents_table.php`
- `create_document_types_table.php`
- `create_field_groups_table.php`
- `create_templates_table.php`
- `create_users_table.php`
- `create_languages_table.php`
- `create_navigations_table.php`

## Database Configuration

Configure your database connection in `config/database.php` and `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inspirecms
DB_USERNAME=root
DB_PASSWORD=
```

Table name prefix can be configured in `config/inspirecms.php`:

```php
'models' => [
    'table_name_prefix' => 'cms_',
    // Other model settings...
],
```

## Data Storage

### JSON Fields

InspireCMS uses JSON fields for storing:

1. **Translatable Content**: Stores multi-language values as JSON objects with locale keys:
   ```json
   {
     "en": "English Title",
     "es": "Título en Español",
     "fr": "Titre en Français"
   }
   ```

2. **Field Configuration**: Stores field type-specific settings:
   ```json
   {
     "min_length": 5,
     "max_length": 100,
     "placeholder": "Enter your text here",
     "default_value": "Default"
   }
   ```

3. **Content Properties**: Stores structured content data:
   ```json
   {
     "hero": {
       "title": {
         "en": "Welcome to our site"
       },
       "image": {
         "id": "550e8400-e29b-41d4-a716-446655440000"
       }
     }
   }
   ```

### Binary Data

Media files are not stored directly in the database. Instead:

- File metadata is stored in the `cms_media_assets` table
- Actual files are stored in the filesystem (local or cloud storage)
- File paths and storage disk information link database records to physical files

## Database Optimization

For optimal performance:

1. **Indexes**: Key columns are indexed for faster querying
2. **Foreign Keys**: Enforce referential integrity
3. **UUID vs Auto-increment**: UUIDs are used for better distribution and scaling
4. **Soft Deletes**: `deleted_at` timestamps allow for data recovery
5. **Query Optimization**: Eager loading relationships reduces N+1 query issues

## Extending the Database Schema

To extend the database for custom functionality:

### Custom Migration Example

```php
use SolutionForest\InspireCms\Support\Base\BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomContentMetadataTable extends BaseMigration
{
    public function up()
    {
        Schema::create($this->prefix . 'custom_content_metadata', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('content_id');
            $table->string('meta_key');
            $table->json('meta_value');
            $table->timestamps();
            
            $table->foreign('content_id')
                  ->references('id')
                  ->on('cms_contents')
                  ->onDelete('cascade');
                  
            $table->index(['content_id', 'meta_key']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists($this->prefix . 'custom_content_metadata');
    }
}
```

### Custom Model Example

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;

class CustomContentMetadata extends BaseModel
{
    use HasUuids;
    
    protected $table = 'custom_content_metadata';
    
    protected $fillable = [
        'content_id',
        'meta_key',
        'meta_value',
    ];
    
    protected $casts = [
        'meta_value' => 'json',
    ];
    
    public function content()
    {
        return $this->belongsTo(\SolutionForest\InspireCms\Models\Content::class);
    }
}
```

## Best Practices

1. **Use Migrations**: Always use migrations for schema changes
2. **Follow Conventions**: Maintain naming and structure conventions
3. **Preserve Relationships**: Maintain referential integrity
4. **Add Indexes**: Index columns used in WHERE, JOIN, or ORDER BY
5. **Document Changes**: Add comments to migrations explaining purpose
6. **Version Control**: Keep migrations in version control
7. **Test Migrations**: Test both up and down migrations
8. **Backup Data**: Always backup before major schema changes
9. **Consider Performance**: Evaluate impact of schema changes on performance