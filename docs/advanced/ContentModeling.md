# Content Modeling

Content modeling in InspireCMS allows you to define structured content types that match your specific requirements. This guide explains how to create and manage content models effectively.

## Overview

Content modeling in InspireCMS is built around:

- **Document Types**: Define the structure and behavior of content
- **Field Groups**: Group related fields together
- **Custom Fields**: Various field types for different content needs
- **Templates**: Define how content is displayed

## Document Types

Document types define the structure and behavior of your content. Each document type specifies which field groups it uses and what templates can be applied to it.

### Creating a Document Type

1. Navigate to **Settings → Document Types** in the admin panel
2. Click **Create Document Type**
3. Fill in the required information:
   - **Title**: User-friendly name (e.g., "Blog Post")
   - **Slug**: Machine name (e.g., "blog_post")
   - **Icon**: Select an icon to represent this type
   - **Category**: Categorize your document types (e.g., "web", "marketing")
   - **Field Groups**: Select which field groups to include
   - **Templates**: Define which templates can be used with this document type
4. Click **Save** to create the document type

### Document Type Options

- **Show as Table**: Display content in table view rather than tree view
- **Show at Root**: Allow content of this type to be created at the root level
- **Default Template**: Set the default template for new content
- **Allowed Types**: Define which document types can be created as children of this type

### Document Type Relationships

Document types can have parent-child relationships:

```php
// config/inspirecms.php
'content' => [
    'document_types' => [
        'blog_post' => [
            'allowed' => [], // No child documents allowed
        ],
        'blog_category' => [
            'allowed' => ['blog_post'], // Can contain blog posts
        ],
        'page' => [
            'allowed' => ['page'], // Pages can contain other pages
        ],
    ],
],
```

## Field Groups

Field groups organize related fields together and can be reused across different document types.

### Creating a Field Group

1. Navigate to **Settings → Field Groups** in the admin panel
2. Click **Create Field Group**
3. Fill in the required information:
   - **Title**: User-friendly name (e.g., "Content Section")
   - **Slug**: Machine name (e.g., "content_section")
   - **Fields**: Define the fields in this group
4. Click **Save** to create the field group

### Field Configuration

Each field has the following properties:

- **Label**: User-friendly name shown in the editor
- **Slug**: Machine name for accessing the field
- **Type**: The field type (text, textarea, select, etc.)
- **Required**: Whether the field is required
- **Translatable**: Whether the field can be translated
- **Help Text**: Additional information for content editors
- **Default Value**: Initial value for the field

### Available Field Types

InspireCMS provides many field types for different content needs:

| Field Type | Description | Configuration Options |
|------------|-------------|------------------------|
| `text` | Single-line text input | Character limit, placeholder |
| `textarea` | Multi-line text input | Character limit, rows |
| `richEditor` | WYSIWYG text editor | Toolbar buttons, allowed HTML tags |
| `markdownEditor` | Markdown text editor | Toolbar buttons |
| `number` | Numeric input | Min, max, step |
| `date` | Date picker | Format, min/max dates |
| `boolean` | Toggle switch | Default value |
| `select` | Dropdown selection | Options, multiple select |
| `tags` | Multiple tag input | Available tags, free entry |
| `mediaPicker` | Media library selection | Allow multiple, file types |
| `contentPicker` | Content relationship | Document types, multiple selection |
| `repeater` | Repeatable field group | Min/max items, add/remove buttons |
| `layout` | Visual layout builder | Available blocks |
| `color` | Color picker | Format (hex, rgb, etc.) |
| `code` | Code editor | Language, theme |

### Creating Custom Field Types

You can create custom field types for specialized content needs:

```php
namespace App\Fields;

use SolutionForest\InspireCms\Fields\AbstractField;

class GoogleMapField extends AbstractField
{
    public static function getType(): string
    {
        return 'googleMap';
    }
    
    public function getFormField(): array
    {
        return [
            \Filament\Forms\Components\Grid::make()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('latitude')
                        ->required()
                        ->numeric()
                        ->label('Latitude'),
                    \Filament\Forms\Components\TextInput::make('longitude')
                        ->required()
                        ->numeric()
                        ->label('Longitude'),
                    \Filament\Forms\Components\TextInput::make('zoom')
                        ->numeric()
                        ->default(10)
                        ->label('Zoom Level'),
                ])
                ->label($this->getLabel()),
        ];
    }
    
    public function getValue($data)
    {
        // Transform stored data into usable format
        return [
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'zoom' => $data['zoom'] ?? 10,
        ];
    }
}
```

Register your custom field type:

```php
// config/inspirecms.php
'custom_fields' => [
    'extra_config' => [
        // Existing field configurations...
        \App\Fields\GoogleMapField::class,
    ],
],
```

## Content Schemas

### Defining Complex Content Structures

For more complex content structures, you can define multiple field groups:

**Article Structure Example**:

1. **Basic Info Field Group**:
   - Title (text, translatable)
   - Slug (text)
   - Author (select or contentPicker)
   - Publication Date (date)

2. **SEO Field Group**:
   - Meta Title (text, translatable)
   - Meta Description (textarea, translatable)
   - Keywords (tags, translatable)
   - Social Image (mediaPicker)

3. **Content Field Group**:
   - Featured Image (mediaPicker)
   - Summary (textarea, translatable)
   - Body Content (richEditor, translatable)

4. **Categorization Field Group**:
   - Categories (contentPicker, multiple)
   - Tags (tags)

5. **Related Content Field Group**:
   - Related Articles (contentPicker, multiple)
   - Recommendation Priority (number)

### Field Group Assignment

Assign field groups to document types based on content needs:

```php
// Document Type: Blog Post
[
    'slug' => 'blog_post',
    'title' => 'Blog Post',
    'fieldGroups' => [
        'basic_info',
        'seo',
        'content',
        'categorization',
        'related_content',
    ],
]

// Document Type: Simple Page
[
    'slug' => 'simple_page',
    'title' => 'Simple Page',
    'fieldGroups' => [
        'basic_info',
        'seo',
        'content',
    ],
]
```

## Content Validation

Define validation rules for your fields:

```php
// Field with validation rules
[
    'slug' => 'email',
    'label' => 'Email Address',
    'type' => 'text',
    'config' => [
        'validation' => [
            'required' => true,
            'email' => true,
            'max' => 255,
        ],
    ],
]

// Number with range validation
[
    'slug' => 'age',
    'label' => 'Age',
    'type' => 'number',
    'config' => [
        'validation' => [
            'required' => true,
            'min' => 18,
            'max' => 120,
        ],
    ],
]
```

## Conditional Fields

You can create conditional fields that appear based on other field values:

```php
// Field that shows conditionally
[
    'slug' => 'notification_email',
    'label' => 'Notification Email',
    'type' => 'text',
    'config' => [
        'visible' => [
            'when' => [
                'send_notifications' => true,
            ],
        ],
        'validation' => [
            'email' => true,
        ],
    ],
]
```

## Content Templates

Templates define how content is displayed on the frontend. Each document type can have multiple templates.

### Creating a Template

1. Navigate to **Settings → Document Types** in the admin panel
2. Select the document type you want to add a template to
3. Click on the **Templates** tab
4. Click **Create Template**
5. Fill in the details:
   - **Name**: Template name (e.g., "Full Width")
   - **Slug**: Machine name (e.g., "full_width")
   - **Description**: Optional explanation of when to use this template

### Template Implementation

Create the template file in your theme directory:

```php
<!-- resources/views/components/inspirecms/your-theme/full-width.blade.php -->
<x-dynamic-component :component="inspirecms_templates()->getComponentWithTheme('layout')">
    <div class="container-fluid">
        <div class="page-header">
            <h1>{{ $content->getTitle() }}</h1>
        </div>
        
        <div class="content-body">
            @property('content', 'body')
        </div>
        
        @if($content->hasProperty('related_content', 'related_articles'))
            <div class="related-articles">
                <h3>Related Articles</h3>
                <div class="row">
                    @foreach($content->getPropertyValue('related_content', 'related_articles') as $article)
                        <div class="col-md-4">
                            <a href="{{ $article->getUrl() }}">
                                {{ $article->getTitle() }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>
```

## Content Modeling Best Practices

1. **Start with Content Strategy**: Understand your content needs before creating models
2. **Focus on Reusability**: Create field groups that can be reused across document types
3. **Balance Flexibility and Structure**: Give content editors freedom while maintaining content integrity
4. **Use Meaningful Names**: Choose clear, descriptive names for document types, field groups, and fields
5. **Plan for Growth**: Design your content models to accommodate future requirements
6. **Document Your Models**: Create documentation explaining the purpose of each field and how it should be used
7. **Limit Required Fields**: Only make fields required when absolutely necessary
8. **Group Related Fields**: Organize fields logically to improve the editing experience
9. **Use Appropriate Field Types**: Select the most suitable field type for each content need

## Advanced Content Modeling

### Nesting Content Models

You can create nested content structures:

```php
// Parent document type
[
    'slug' => 'product_category',
    'title' => 'Product Category',
    'allowed' => ['product'], // Can contain products
]

// Child document type
[
    'slug' => 'product',
    'title' => 'Product',
    'allowed' => [], // Cannot contain other content
]
```

### Designing for Flexibility

Use repeater fields for flexible content sections:

```php
// Flexible content field group
[
    'slug' => 'page_builder',
    'title' => 'Page Builder',
    'fields' => [
        [
            'slug' => 'sections',
            'label' => 'Content Sections',
            'type' => 'repeater',
            'config' => [
                'types' => [
                    'text_block' => [
                        'label' => 'Text Block',
                        'fields' => [
                            [
                                'slug' => 'heading',
                                'label' => 'Heading',
                                'type' => 'text',
                            ],
                            [
                                'slug' => 'content',
                                'label' => 'Content',
                                'type' => 'richEditor',
                            ],
                        ],
                    ],
                    'image_gallery' => [
                        'label' => 'Image Gallery',
                        'fields' => [
                            [
                                'slug' => 'title',
                                'label' => 'Gallery Title',
                                'type' => 'text',
                            ],
                            [
                                'slug' => 'images',
                                'label' => 'Gallery Images',
                                'type' => 'mediaPicker',
                                'config' => [
                                    'multiple' => true,
                                ],
                            ],
                        ],
                    ],
                    'call_to_action' => [
                        'label' => 'Call to Action',
                        'fields' => [
                            [
                                'slug' => 'title',
                                'label' => 'CTA Title',
                                'type' => 'text',
                            ],
                            [
                                'slug' => 'button_text',
                                'label' => 'Button Text',
                                'type' => 'text',
                            ],
                            [
                                'slug' => 'button_url',
                                'label' => 'Button URL',
                                'type' => 'text',
                            ],
                            [
                                'slug' => 'background_color',
                                'label' => 'Background Color',
                                'type' => 'color',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
]
```

### Creating Taxonomy Systems

For categorization and organization:

```php
// Create a taxonomy system for products
// Document Type: Product Category
[
    'slug' => 'product_category',
    'title' => 'Product Category',
    'showAsTable' => false,
    'fieldGroups' => ['category_info'],
    'allowed' => ['product_category', 'product'], // Can contain other categories or products
]

// Field Group: Category Info
[
    'slug' => 'category_info',
    'title' => 'Category Information',
    'fields' => [
        [
            'slug' => 'description',
            'label' => 'Description',
            'type' => 'textarea',
            'config' => [
                'translatable' => true,
            ],
        ],
        [
            'slug' => 'featured_image',
            'label' => 'Featured Image',
            'type' => 'mediaPicker',
        ],
    ],
]
```

## Troubleshooting Content Models

### Fields Not Appearing

If fields are not appearing in the content editor:

1. Verify that the field group is assigned to the document type
2. Check for conditional visibility rules that might be hiding fields
3. Ensure the field is defined correctly in the field group

### Content Not Saving

If content isn't saving properly:

1. Check for validation errors in the form
2. Verify that required fields are filled in
3. Check for field type mismatches (e.g., text in a number field)

### Template Issues

If templates are not working correctly:

1. Ensure the template is assigned to the document type
2. Check that the template file exists in the correct location
3. Verify that the template is accessing fields using the correct field group and field names