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
| `document_type_id` | UUID | Reference to document type |
| `title` | JSON | Translatable content title |
| `slug` | String | URL-friendly identifier |
| `status` | Integer | Content status (draft, published, etc.) |
| `created_by` | UUID | User who created the content |
| `updated_by` | UUID | User who last updated the content |
| `published_at` | Timestamp | When content was published |
| `is_default` | Boolean | Whether this is the default content |
| `position` | Integer | Sorting position |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |
| `deleted_at` | Timestamp | Soft delete timestamp |

#### `cms_content_paths`

Maps content to their hierarchical paths in the site structure.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `content_id` | UUID | Reference to content |
| `path` | String | Full path to content |
| `depth` | Integer | Nesting depth |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_content_routes`

Defines URL routes to content.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `content_id` | UUID | Reference to content |
| `language_id` | UUID | Reference to language |
| `route_pattern` | String | URL pattern |
| `is_default` | Boolean | Whether this is the default route |
| `regex_constraints` | JSON | Route pattern constraints |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_document_types`

Defines the different types of content in the system.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `name` | JSON | Translatable name |
| `slug` | String | URL-friendly identifier |
| `description` | JSON | Translatable description |
| `icon` | String | Icon identifier |
| `show_as_table` | Boolean | Display mode in admin UI |
| `show_at_root` | Boolean | Whether to show at root level |
| `category` | String | Document type category |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

### Field Management

#### `cms_field_groups`

Defines groups of fields for content types.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `name` | JSON | Translatable name |
| `slug` | String | URL-friendly identifier |
| `description` | JSON | Translatable description |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_fields`

Defines individual fields within field groups.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `field_group_id` | UUID | Reference to field group |
| `name` | JSON | Translatable name |
| `slug` | String | URL-friendly identifier |
| `description` | JSON | Translatable description |
| `type` | String | Field type identifier |
| `is_required` | Boolean | Whether field is required |
| `is_translatable` | Boolean | Whether field supports translation |
| `config` | JSON | Field configuration options |
| `position` | Integer | Sorting position |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_field_groupables`

Polymorphic pivot table linking field groups to content types, templates, etc.

| Column | Type | Description |
|--------|------|-------------|
| `field_group_id` | UUID | Reference to field group |
| `field_groupable_type` | String | Polymorphic type |
| `field_groupable_id` | UUID | Polymorphic ID |
| `position` | Integer | Sorting position |

### Template Management

#### `cms_templates`

Stores template definitions.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `document_type_id` | UUID | Reference to document type |
| `name` | JSON | Translatable name |
| `slug` | String | URL-friendly identifier |
| `description` | JSON | Translatable description |
| `content` | Text | Template content |
| `is_default` | Boolean | Whether this is the default template |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_templateables`

Polymorphic pivot table linking templates to content.

| Column | Type | Description |
|--------|------|-------------|
| `template_id` | UUID | Reference to template |
| `templateable_type` | String | Polymorphic type |
| `templateable_id` | UUID | Polymorphic ID |

### User Management

#### `cms_users`

Stores user accounts.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `name` | String | User's full name |
| `email` | String | User's email address |
| `password` | String | Hashed password |
| `remember_token` | String | "Remember me" token |
| `email_verified_at` | Timestamp | When email was verified |
| `last_login_at` | Timestamp | Last login timestamp |
| `settings` | JSON | User-specific settings |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_roles`

Defines user roles.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `name` | String | Role name |
| `guard_name` | String | Authentication guard |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_permissions`

Defines individual permissions.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `name` | String | Permission name |
| `guard_name` | String | Authentication guard |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_model_has_roles`

Links users to roles.

| Column | Type | Description |
|--------|------|-------------|
| `role_id` | UUID | Reference to role |
| `model_type` | String | Polymorphic type |
| `model_id` | UUID | Polymorphic ID |

#### `cms_role_has_permissions`

Links roles to permissions.

| Column | Type | Description |
|--------|------|-------------|
| `permission_id` | UUID | Reference to permission |
| `role_id` | UUID | Reference to role |

### Media Management

#### `cms_media_assets`

Stores media assets.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `disk` | String | Storage disk identifier |
| `directory` | String | Directory path |
| `filename` | String | File name |
| `extension` | String | File extension |
| `mime_type` | String | MIME type |
| `size` | Integer | File size in bytes |
| `alt_text` | JSON | Translatable alternative text |
| `title` | JSON | Translatable title |
| `caption` | JSON | Translatable caption |
| `description` | JSON | Translatable description |
| `metadata` | JSON | Additional metadata |
| `created_by` | UUID | User who uploaded the asset |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

### Site Configuration

#### `cms_languages`

Defines available languages.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `name` | String | Language name |
| `locale` | String | Language code |
| `is_default` | Boolean | Whether this is the default language |
| `is_active` | Boolean | Whether language is active |
| `direction` | String | Text direction (ltr/rtl) |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_navigations`

Stores navigation menu structure.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `parent_id` | UUID | Parent navigation item ID |
| `category` | String | Navigation category (main, footer, etc.) |
| `title` | JSON | Translatable title |
| `url` | String | Direct URL (for external links) |
| `type` | String | Link type (content, url, group) |
| `content_id` | UUID | Reference to content (for content links) |
| `target` | String | Link target (_blank, _self, etc.) |
| `position` | Integer | Sorting position |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Last update timestamp |

#### `cms_key_values`

Stores key-value pairs for application settings.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID | Primary key |
| `key` | String | Setting key |
| `value` | JSON | Setting value |
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
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomContentMetadataTable extends Migration
{
    public function up()
    {
        Schema::create('cms_custom_content_metadata', function (Blueprint $table) {
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
        Schema::dropIfExists('cms_custom_content_metadata');
    }
}
```

### Custom Model Example

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CustomContentMetadata extends Model
{
    use HasUuids;
    
    protected $table = 'cms_custom_content_metadata';
    
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