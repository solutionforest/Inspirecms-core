# Custom Fields

Custom fields allow you to define structured content types in InspireCMS. This guide explains how to create, configure, and use custom fields to build flexible content models.

## Field Types Overview

InspireCMS offers a wide range of field types for different content needs:

| Field Type | Description | Example Use |
|------------|-------------|-------------|
| Text | Single-line text input | Titles, headings, names |
| Text Area | Multi-line text input | Descriptions, short paragraphs |
| Rich Editor | WYSIWYG HTML editor | Formatted content bodies |
| Markdown Editor | Markdown text editor | Technical documentation |
| Email | Validated email input | Contact information |
| URL | Validated URL input | External links |
| Number | Numeric input | Quantities, ratings |
| Select | Dropdown selection | Categories, statuses |
| Toggle | On/off switch | Feature flags, visibility settings |
| Radio | Radio button group | Mutually exclusive options |
| File | File upload | Documents, downloads |
| Image | Image upload with preview | Photos, illustrations |
| Color Picker | Color selection tool | Theme colors, text highlights |
| DateTime Picker | Date/time selector | Publication dates, event times |
| Content Picker | Reference other content | Related articles, products |
| Media Picker | Select from media library | Gallery images, videos |
| Repeater | Group of repeatable fields | Team members, features list |
| Tags | Multiple keyword input | Blog tags, product attributes |

## Creating Field Groups

Field groups organize related fields together. To create a field group:

1. Navigate to **Settings** > **Custom Fields** in the admin panel
2. Click **Create Field Group**
3. Enter a name and slug for your field group
4. Add fields to the group using the form

### Field Configuration Options

Each field type has specific configuration options, but most share these common settings:

- **Label**: The display name shown to content editors
- **Name**: The technical identifier used in templates
- **Helper Text**: Additional guidance shown below the field
- **Required**: Whether the field must be filled
- **Translatable**: Whether the field should be multilingual

### Field Type Specific Settings

#### Rich Editor
- **Toolbar Buttons**: Customize available formatting options
- **Character Limit**: Set maximum content length
- **Media Upload**: Enable/disable image embedding

#### Repeater
- **Field Types**: Define the sub-fields within each repeated item
- **Min/Max Items**: Control how many items can be added
- **Collapsible**: Allow collapsing of items for better organization

#### Select
- **Options**: Define available choices
- **Multiple**: Allow multiple selections
- **Default Value**: Pre-select values

## Using Fields in Document Types

After creating field groups, associate them with document types:

1. Navigate to **Settings** > **Document Types**
2. Create or edit a document type
3. Under "Field Groups," select the relevant field groups
4. Save your document type

## Accessing Fields in Templates

InspireCMS provides several directives to access field data in templates:

### Basic Field Access

```php
@property('field_group_name', 'field_name')
```

This outputs the field value and creates a variable `$field_group_name_field_name` accessible in your template.

### Conditional Field Access

```php
<?php
@propertyNotEmpty('field_group_name', 'field_name')
    <h2>{{ $field_group_name_field_name }}</h2>
@endif
```

This checks if the field has a value before rendering content.

### Accessing Arrays

```php
<?php
@propertyArray('field_group_name', 'field_name')
@foreach($field_group_name_field_name as $item)
    <div>{{ $item }}</div>
@endforeach
```

This is useful for repeaters, tags, and other multi-value fields.

### Alternative Access Pattern

You can also access field data through the content object:

```php
<?php
{{ $content->getPropertyGroup('field_group_name')->getPropertyData('field_name')->getValue() }}
```

## Creating Custom Field Types

InspireCMS allows you to create custom field types for specialized needs:

1. Create a field type configuration class
2. Register the field type in your service provider

Example configuration class:

```php
<?php
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Translatable;

#[ConfigName('my_custom_field', 'My Custom Field', 'Custom Fields', 'heroicon-o-star')]
#[FormComponent(\Filament\Forms\Components\TextInput::class)]
#[DbType('mysql', 'text')]
#[Translatable(true)]
class MyCustomFieldConfig extends FieldTypeBaseConfig implements FieldTypeConfig
{
    // Field configuration
}
```

Register in service provider:

```php
<?php
public function boot()
{
    FilamentFieldGroup::fieldTypeConfigs([
        MyCustomFieldConfig::class,
    ]);
}
```

## Field Value Converters

Value converters transform field data between storage format and display format:

```php
<?php
use SolutionForest\InspireCms\Fields\Converters\BaseConverter;

class MyCustomConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        // Transform the value for display
        return $this->processValue($sourceValue);
    }
}
```

## Best Practices
* Keep field groups organized by logical function (e.g., "SEO", "Banner", "Content")
* Use clear, descriptive names for fields and field groups
* Add helper text to guide content editors
* Use appropriate validation rules to ensure data quality
* Leverage translatable fields for multilingual content
* Consider the frontend display when designing field structures

## Next Steps

Learn more about:

* Document Types - Using fields in document types
* Templating - Advanced template techniques
* Themes - Building theme layouts