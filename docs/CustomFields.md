# Custom Fields

InspireCMS provides a flexible system for defining and managing fields in your content. Custom fields allow you to customize the data entry process for your documents. This guide explains how to choose a field type, create a custom field, and configure its settings.

## Dependency

This custom fields system is built on top of the [Filament Field Group](https://github.com/solutionforest/filament-field-group) package. It leverages the powerful field configuration and management capabilities provided by this package. 

### Key Features from Filament Field Group
- **Dynamic Field Groups**: Easily define and manage groups of fields.
- **Extensible Field Types**: Support for a wide range of field types, including custom ones.
- **Validation Rules**: Built-in support for Laravel validation rules.
- **Translatable Fields**: Seamless integration for multi-locale content.

To learn more about the underlying functionality, refer to the [Filament Field Group documentation](https://github.com/solutionforest/filament-field-group).

## Field Types

Choosing a field type determines:

- **Input UI:** How users interact with and input data into the field.
- **Data Storage:** How the field's data is stored in the system.
- **Template Usage:** How you reference and display this data in your templates.

Below is a list of available field types:

| Field Type | Description |
|------------|-------------|
| Text              | A single-line text input field for short pieces of plain text. |
| Text Area         | A multi-line text input field for longer entries or paragraphs. |
| Email             | An input field that validates an email address format. |
| Password          | A secure input field where entered text is obscured for privacy. |
| Number            | A field that accepts numeric input, often with optional min/max constraints. |
| URL               | An input field for entering hyperlinks, with built-in URL validation. |
| Select            | A dropdown list that lets users choose from predefined options. |
| Toggle            | A binary switch used to represent on/off or true/false choices. |
| Radio             | A set of radio buttons allowing the selection of one option among several choices. |
| File              | A field for uploading files, supporting various file types from the user's system. |
| Image             | A specialized file field tailored for image uploads, often providing a preview. |
| Color Picker      | A field that opens a color selection tool to choose a color value. |
| DateTime Picker   | A combined date and time selector for scheduling or logging events. |
| Content Picker    | A tool to link or reference other internal content items within the system. |
| Media Picker      | A field for selecting media files from a designated media library. |
| Repeater          | A flexible field that allows the user to add multiple sets of sub-fields, useful for repeating data groups. |
| Tags              | An input field designed to add multiple keywords or tags, often with auto-suggestion. |
| Rich Editor       | A WYSIWYG editor that provides formatting tools for styled content creation. |
| Markdown Editor   | An editor focused on Markdown formatting, offering syntax highlighting and preview options. |


## Field Type Attributes

Field types in InspireCMS are defined by a set of attributes that control their behavior, storage, and presentation. These attributes ensure seamless integration with the system.

### Core Attributes

- **ConfigName**: A unique identifier for the field type configuration. This is used internally to load the appropriate configuration class for each field type.
- **DbType**: Specifies how the field's data is stored in the database, including the data type and structure.
- **FormComponent**: Defines the Filament form component used to render the field in the admin interface, controlling the user interface for content editors.

### Additional Attributes

- **Translatable**: Indicates whether the field supports multilingual content. If enabled, the system stores separate values for each configured language.
- **Converter**: A class responsible for transforming data between its raw database format and the format used in templates. Converters handle data processing during both saving and retrieval.

### How Attributes Work Together

These attributes work together to provide a cohesive experience:

1. **ConfigName**: Identifies the field type and loads its configuration.
2. **DbType**: Determines the database column type for storing the field's data.
3. **FormComponent**: Specifies the UI element displayed in the admin panel.
4. **Translatable**: Enables multilingual support by storing values for each language.
5. **Converter**: Transforms data between its storage format and display format.

### Example Configuration

For a simple text field:

- **ConfigName**: `text`
- **DbType**: `string`
- **FormComponent**: `TextInput::class`
- **Translatable**: `true` (if multilingual support is required)
- **Converter**: `TextConverter::class`

This configuration creates a text input field in the admin interface that stores string data in the database and optionally supports multiple languages.

### Summary

By combining these attributes, InspireCMS ensures that field types are flexible, extensible, and easy to use, both for developers and content editors.

## Field Configuration

Each field type can be configured using the following common options:

- **Label**: The display name of the field, visible to users.
- **Name**: A unique identifier for the field, used internally.
- **Helper Text**: Additional text displayed below the field to guide users on what to input.
- **Is Mandatory**: Specifies whether the field must be filled before saving. This is a simpler alternative to using 'required' in validation rules.

## Field Type Configuration and Usage

### Text
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Default Value**: The initial value of the field.
> * **Placeholder**: Text displayed inside the field when empty.
> * **Prefix Label**: Text displayed before the field.
> * **Suffix Label**: Text displayed after the field.
> * **Rule**: Validation rules for the field.
> * **Length**: The exact length of the input.
> * **Max Length**: The maximum allowed length of the input.
> * **Min Length**: The minimum required length of the input.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @property('hero', 'title')
> ```
> </details>

### Text Area
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Rows**: The number of visible rows in the text area.
> * **Default Value**: The initial value of the field.
> * **Placeholder**: Text displayed inside the field when empty.
> * **Rule**: Validation rules for the field.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @property('intro', 'text')
> ```
> </details>

### Email
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Default Value**: The initial value of the field.
> * **Placeholder**: Text displayed inside the field when empty.
> * **Prefix Label**: Text displayed before the field.
> * **Suffix Label**: Text displayed after the field.
> * **Rule**: Validation rules for the field.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @property('contact', 'email')
> ```
> </details>

### Password
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Placeholder**: Text displayed inside the field when empty.
> * **Rule**: Validation rules for the field.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @property('user', 'password')
> ```
> </details>

### Number
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Default Value**: The initial value of the field.
> * **Placeholder**: Text displayed inside the field when empty.
> * **Prefix Label**: Text displayed before the field.
> * **Suffix Label**: Text displayed after the field.
> * **Rule**: Validation rules for the field.
> * **Min Value**: The minimum allowed value.
> * **Max Value**: The maximum allowed value.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @property('event', 'max_ppl')
> ```
> </details>

### URL
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Default Value**: The initial value of the field.
> * **Placeholder**: Text displayed inside the field when empty.
> * **Prefix Label**: Text displayed before the field.
> * **Suffix Label**: Text displayed after the field.
> * **Rule**: Validation rules for the field.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @property('contact', 'facebook')
> ```
> </details>

### Select
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Options**: The predefined choices available in the dropdown.
> * **Multiple**: Whether multiple selections are allowed.
> * **Default Value**: The initial value of the field.
> * **Rule**: Validation rules for the field.
> </details>
> <details><summary>Template Usage</summary>
>
> Multiple is false:
> ```php
> @property('event', 'type')
> ```
> Multiple is true:
> ```php
> @propertyArray('event', 'types')
> {{ implode(', ', $event_types ?? []) }}
> ```
> </details>

### Toggle
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @if ($content?->getPropertyGroup('event')?->getPropertyData('active')?->getValue() ?? false)
> @endif
> ```
> </details>

### Radio
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Options**: The predefined choices available for selection.
> * **Default Value**: The initial value of the field.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @property('user', 'gender')
> ```
> </details>

### File
>
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Disk**: The storage disk where files are saved.
> * **Directory**: The directory path within the disk.
> * **Visibility**: The visibility of the uploaded files.
> * **Multiple**: Whether multiple files can be uploaded.
> * **Rule**: Validation rules for the field.
> * **Accepted File Types**: The allowed file types for upload.
> * **Min File**: The minimum number of files required.
> * **Max File**: The maximum number of files allowed.
> * **Min Size**: The minimum file size allowed.
> * **Max Size**: The maximum file size allowed.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @propertyArray('event', 'docs')
> @foreach ($event_docs ?? [] as $doc)
>     <a href="{{ \Storage::disk($doc->disk)->url(implode('/', array_filter([$doc->directory, $doc->path], 'filled'))) }}">Doc</a>
> @endforeach
> ```
> Or
> ```php
> @propertyArray('event', 'docs')
> @foreach ($event_docs ?? [] as $doc)
>     <a href="{{ $doc }}">Doc</a>
> @endforeach
> ```
> </details>

### Image
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Disk**: The storage disk where images are saved.
> * **Directory**: The directory path within the disk.
> * **Visibility**: The visibility of the uploaded images.
> * **Multiple**: Whether multiple images can be uploaded.
> * **Rule**: Validation rules for the field.
> * **Accepted File Types**: The allowed file types for upload.
> * **Min File**: The minimum number of images required.
> * **Max File**: The maximum number of images allowed.
> * **Min Size**: The minimum image size allowed.
> * **Max Size**: The maximum image size allowed.
> </details>
> <details><summary>Template Usage</summary>
>
> ```php
> @propertyArray('event', 'images')
> @foreach ($event_images ?? [] as $img)
>     <img src="{{ \Storage::disk($img->disk)->url(implode('/', array_filter([$img->directory, $img->path], > 'filled'))) }}" alt="Event Image">
> @endforeach
> ```
> Or
> ```php
> @propertyArray('event', 'images')
> @foreach ($event_images ?? [] as $img)
>     <img src="{{ $img }}" alt="Event Image">
> @endforeach
> ```
> </details>

### Color Picker
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Default Value**: The initial value of the field.
> </details>
><details><summary>Template Usage</summary>
>
>  ```php
>  @propertyNotEmpty('user', 'fav_color')
>  <p style="color: {{ $user_fav_color }}">$user_fav_color</p>
>  @endif
>  ```
></details>

### DateTime Picker
> <details><summary>Configuration Options</summary>
>
> * **Translatable**: Whether the field supports multiple languages.
> * **Default Value**: The initial value of the field.
> * **Placeholder**: Text displayed inside the field when empty.
> * **Prefix Label**: Text displayed before the field.
> * **Suffix Label**: Text displayed after the field.
> * **Has Time**: Whether the field includes time selection.
> * **Has Date**: Whether the field includes date selection.
> * **Rule**: Validation rules for the field.
> * **Format**: The format of the date/time value.
> </detail>
> <details><summary>Template Usage</summary>
> 
> ```php
> @propertyNotEmpty('event', 'date')
> <p>Year: {{ $event_date?->format('Y') }}</p>
> @endif
> ```
> </details>

### Content Picker

> <details><summary>Configuration Options</summary>
> 
> * **Translatable**: Whether the field supports multiple languages.
> * **Types**: The types of media files allowed.
> * **Min**: The minimum number of items required.
> * **Max**: The maximum number of items allowed.
> </details>
> <details><summary>Template Usage</summary>
> 
> ```php
>  @propertyArray('hero', 'image_slider')
>  @foreach ($hero_image_slider ?? [] as $item)
>      <div class="swiper-slide">
>          <img src="{{ $item?->getUrl() }}" alt="Slide {{ $loop->iteration }}">
>          <p>{{ $item?->description }}</p>
>      </div>
>  @endforeach
> ```
> </details>

### Media Picker

> <details><summary>Configuration Options</summary>
> 
> * **Translatable**: Whether the field supports multiple languages.
> * **Types**: The types of media files allowed.
> * **Min**: The minimum number of items required.
> * **Max**: The maximum number of items allowed.
> </details>
> <details><summary>Template Usage</summary>
> 
> ```php
>  @propertyArray('hero', 'image_slider')
>  @foreach ($hero_image_slider ?? [] as $item)
>      <div class="swiper-slide">
>          <img src="{{ $item?->getUrl() }}" alt="Slide {{ $loop->iteration }}">
>          <p>{{ $item?->description }}</p>
>      </div>
>  @endforeach
> ```
> </details>

### Repeater

> 
> <details><summary>Configuration Options</summary>
> 
> * **Fields**: The sub-fields included in the repeater.
> * **Collapsible**: Whether the repeater sections can be collapsed.
> * **Cloneable**: Whether the repeater sections can be cloned.
> </details>
> <details><summary>Template Usage</summary>
> 
> ```php
> @propertyArray('document_content', 'sections')
> @foreach ($document_content_sections ?? [] as $item)
>     <section>
>         <h1>{{ $item->getPropertyData('title')?->getValue() }}</h1>
>         <p>{{ $item->getPropertyData('description')?->getValue() }}</p>
>         {{ $item->getPropertyData('content')?->getValue() ?? '' }}
>     </section>
> @endforeach
> ```
> </details>

### Tags
> 
> <details><summary>Configuration Options</summary>
> 
> * **Translatable**: Whether the field supports multiple languages.
> * **Prefix Label**: Text displayed before the field.
> * **Suffix Label**: Text displayed after the field.
> * **Prefix**: Text added before each tag.
> * **Suffix**: Text added after each tag.
> * **Separator**: The character used to separate tags.
> * **Suggestions**: Predefined suggestions for tags.
> * **Reorderable**: Whether tags can be reordered.
> * **Color**: The color of the tags.
> * **Rule**: Validation rules for the field.
> </details>
> <details><summary>Template Usage</summary>
> 
> ```php
> @propertyArray('event', 'categories')
> <p>{{ implode(' | ', $event_categories ?? []) }}</p>
> ```
> </details>

### Rich Editor
> 
> <details><summary>Configuration Options</summary>
> 
> * **Translatable**: Whether the field supports multiple languages.
> * **Toolbar** Buttons: The buttons available in the editor toolbar.
> * **Disk**: The storage disk where files are saved.
> * **Directory**: The directory path within the disk.
> * **Visibility**: The visibility of the uploaded files.
> </details>
> <details><summary>Template Usage</summary>
> 
> ```php
> @property('event', 'content')
> ```
> </details>

### Markdown Editor
> 
> <details><summary>Configuration Options</summary>
> 
> * **Translatable**: Whether the field supports multiple languages.
> * **Toolbar** Buttons: The buttons available in the editor toolbar.
> * **Disk**: The storage disk where files are saved.
> * **Directory**: The directory path within the disk.
> * **Visibility**: The visibility of the uploaded files.
> </details>
> <details><summary>Template Usage</summary>
> 
> ```php
> @property('document', 'content')
> ```
> </details>

## Using Fields in Templates

Fields can be accessed in templates using the `@property` directives. For more details, refer to the [Templating documentation](./Templating.md).

```php
<h1>@property('hero', 'title')</h1>
<p>@property('hero', 'subtitle')</p>
@propertyNotEmpty('hero', 'image')
    <img src="{{ \Arr::first($hero_image) }}" alt="@property('hero', 'image_alt')">
@endif
```

## How to Add Extra Field Types

InspireCMS allows you to extend the available field types to meet custom requirements. This involves creating a field type configuration class and registering it with the system.

### Step 1: Create a Field Type Configuration

Define a class that extends `FieldTypeBaseConfig` and implements `FieldTypeConfig`. Use attributes to specify the field's configuration.

```php
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Translatable;

#[ConfigName('customField', 'Custom Field', 'Custom', 'heroicon-o-tag')]
#[FormComponent(\Filament\Forms\Components\TextInput::class)]
#[DbType('mysql', 'text')]
#[DbType('sqlite', 'text')]
#[Translatable(true)]
class CustomFieldConfig extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public $defaultValue = null;
    public $placeholder = null;

    public function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\TextInput::make('defaultValue'),
            \Filament\Forms\Components\TextInput::make('placeholder'),
        ];
    }

    public function applyConfig(\Filament\Forms\Components\Component $component): void
    {
        if ($component instanceof \Filament\Forms\Components\TextInput) {
            if (filled($this->defaultValue)) {
                $component->default($this->defaultValue);
            }
            if (filled($this->placeholder)) {
                $component->placeholder($this->placeholder);
            }
        }
    }
}
```

### Step 2: Register the Field Type

Once you have created the custom field type configuration, you need to register it with the system. There are two ways to do this:

#### Option 1: Register in a Service Provider

You can register the custom field type in the `boot` method of a service provider. This approach is useful if you want to keep the registration logic within your application's service providers.

```php
// ...existing code...
use SolutionForest\FilamentFieldGroup\Facades\FilamentFieldGroup;

public function boot()
{
    FilamentFieldGroup::fieldTypeConfigs([
        CustomFieldConfig::class, // Add your custom field configuration here
    ], override: false);
}
// ...existing code...
```

#### Option 2: Register in the Configuration File

Alternatively, you can register the custom field type in the `custom_fields.extra_config` array of the `config/inspirecms.php` file. This approach centralizes the configuration and is easier to manage for multiple custom field types.

```php
// filepath: config/inspirecms.php
// ...existing code...
'custom_fields' => [
    'extra_config' => [
        CustomFieldConfig::class, // Add your custom field configuration here
        // ...existing field configurations...
    ],
],
```

### Summary

By registering the custom field type using either of these methods, you ensure that InspireCMS recognizes and integrates your custom field type seamlessly. Choose the method that best fits your application's structure and requirements.

## Field Type Converters

Field type converters are responsible for transforming data between the raw format stored in the database and the format used in templates. They play a crucial role in ensuring data is properly processed, validated, and formatted throughout the content lifecycle.

### Purpose of Converters

- **Data Transformation**: Convert between database storage format and usable application format
- **Type Casting**: Ensure data is of the correct PHP type when used in templates
- **Value Preparation**: Handle any necessary pre-processing before storage or display

### Built-in Converters

InspireCMS includes several built-in converters for common field types:

| Converter | Purpose |
|-----------|---------|
| DefaultConverter | Basic conversion for simple field types like text and numbers |
| DateTimeConverter | Converts between string dates and DateTime objects |
| ContentPickerConverter | Transforms content references into usable content objects |
| FileConverter | Handles file path conversions and URL generation |
| MarkdownConverter | Processes markdown text into HTML for display |
| MediaPickerConverter | Manages media asset conversions and metadata |
| RepeaterConverter | Processes nested field groups within repeater fields |
| RichEditorConverter | Handles HTML content sanitization and processing |

### Creating Custom Converters

To create a custom converter, extend the base `FieldConverter` class and implement the necessary transformation methods:

```php
use SolutionForest\InspireCms\Fields\Converters\BaseConverter;

class CustomConverter extends BaseConverter
{
    public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        // Transform the database value into a display format
        // Example: Format a number, transform JSON, etc.
        return $this->processValue($sourceValue);
    }
    
    protected function processValue($value)
    {
        // Add your custom processing logic here
        return $value;
    }
}
```

Then, apply your converter to a field type using the `Converter` attribute:

```php
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Converter;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Translatable;

#[ConfigName('customField', 'Custom Field', 'Custom', 'heroicon-o-tag')]
#[FormComponent(\Filament\Forms\Components\TextInput::class)]
#[DbType('mysql', 'text')]
#[DbType('sqlite', 'text')]
#[Converter(\App\Support\Converters\CustomConverter::class)]
#[Translatable(true)]
class CustomFieldConfig extends FieldTypeBaseConfig implements FieldTypeConfig
{
    // Field configuration implementation
}
```

## Macros

The Field Type system supports macros, allowing you to extend functionality without creating full custom field types. This approach is useful for adding small enhancements or modifications to existing field types.

### Adding Macros

You can use the `mixin` method to add multiple macros at once:

```php
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;

// Add multiple methods via a mixin class
FieldTypeBaseConfig::mixin(new \Your\Mixins\FieldMixin);
```

Or add individual macros using the `macro` method:

```php
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;

// Add a single macro
FieldTypeBaseConfig::macro('addHelpText', function ($text) {
    $this->helperText($text);
    return $this;
});
```

### Usage Examples

#### Enhancing Field Validation

```php
FieldTypeBaseConfig::macro('requireHttps', function () {
    $this->rule('regex:/^https:\/\/.+$/');
    $this->helperText('Must be an HTTPS URL');
    return $this;
});

// Usage in field configuration
$field->requireHttps();
```

#### Adding Custom UI Behavior

```php
FieldTypeBaseConfig::macro('withCharacterCount', function () {
    $this->extraAttributes([
        'x-data' => '{
            charCount: 0,
            updateCount: function(el) { this.charCount = el.value.length; }
        }',
        'x-init' => 'updateCount($el)',
        'x-on:keyup' => 'updateCount($el)',
    ]);
    $this->helperText('Character count: <span x-text="charCount"></span>');
    return $this;
});

// Usage in field configuration
$field->withCharacterCount();
```

Macros provide a lightweight way to extend field functionality without creating full custom field types, making them ideal for project-specific customizations and reusable functionality.

## Additional Resources

- [Templating Documentation](./Templating.md)