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

### Example Field Configurations

Below are examples of how different field types can be configured and used in templates:

<details>
    <summary><h3>Text</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Default Value: The initial value of the field.</li>
            <li>Placeholder: Text displayed inside the field when empty.</li>
            <li>Prefix Label: Text displayed before the field.</li>
            <li>Suffix Label: Text displayed after the field.</li>
            <li>Rule: Validation rules for the field.</li>
            <li>Length: The exact length of the input.</li>
            <li>Max Length: The maximum allowed length of the input.</li>
            <li>Min Length: The minimum required length of the input.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @property('hero', 'title')
        ```
    </details>
</details>

<details>
    <summary><h3>Text Area</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Rows: The number of visible rows in the text area.</li>
            <li>Default Value: The initial value of the field.</li>
            <li>Placeholder: Text displayed inside the field when empty.</li>
            <li>Rule: Validation rules for the field.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @property('intro', 'text')
        ```
    </details>
</details>

<details>
    <summary><h3>Email</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Default Value: The initial value of the field.</li>
            <li>Placeholder: Text displayed inside the field when empty.</li>
            <li>Prefix Label: Text displayed before the field.</li>
            <li>Suffix Label: Text displayed after the field.</li>
            <li>Rule: Validation rules for the field.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @property('contact', 'email')
        ```
    </details>
</details>

<details>
    <summary><h3>Password</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Placeholder: Text displayed inside the field when empty.</li>
            <li>Rule: Validation rules for the field.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @property('user', 'password')
        ```
    </details>
</details>

<details>
    <summary><h3>Number</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Default Value: The initial value of the field.</li>
            <li>Placeholder: Text displayed inside the field when empty.</li>
            <li>Prefix Label: Text displayed before the field.</li>
            <li>Suffix Label: Text displayed after the field.</li>
            <li>Rule: Validation rules for the field.</li>
            <li>Min Value: The minimum allowed value.</li>
            <li>Max Value: The maximum allowed value.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @property('event', 'max_ppl')
        ```
    </details>
</details>

<details>
    <summary><h3>URL</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Default Value: The initial value of the field.</li>
            <li>Placeholder: Text displayed inside the field when empty.</li>
            <li>Prefix Label: Text displayed before the field.</li>
            <li>Suffix Label: Text displayed after the field.</li>
            <li>Rule: Validation rules for the field.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @property('contact', 'facebook')
        ```
    </details>
</details>

<details>
    <summary><h3>Select</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Options: The predefined choices available in the dropdown.</li>
            <li>Multiple: Whether multiple selections are allowed.</li>
            <li>Default Value: The initial value of the field.</li>
            <li>Rule: Validation rules for the field.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        Multiple is false:
        ```php
        @property('event', 'type')
        ```
        Multiple is true:
        ```php
        @propertyArray('event', 'types')
        {{ implode(', ', $event_types ?? []) }}
        ```
    </details>
</details>

<details>
    <summary><h3>Toggle</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @if ($content?->getPropertyGroup('event')?->getPropertyData('active')?->getValue() ?? false)
        @endif
        ```
    </details>
</details>

<details>
    <summary><h3>Radio</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Options: The predefined choices available for selection.</li>
            <li>Default Value: The initial value of the field.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @property('user', 'gender')
        ```
    </details>
</details>

<details>
    <summary><h3>File</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Disk: The storage disk where files are saved.</li>
            <li>Directory: The directory path within the disk.</li>
            <li>Visibility: The visibility of the uploaded files.</li>
            <li>Multiple: Whether multiple files can be uploaded.</li>
            <li>Rule: Validation rules for the field.</li>
            <li>Accepted File Types: The allowed file types for upload.</li>
            <li>Min File: The minimum number of files required.</li>
            <li>Max File: The maximum number of files allowed.</li>
            <li>Min Size: The minimum file size allowed.</li>
            <li>Max Size: The maximum file size allowed.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @propertyArray('event', 'docs')
        @foreach ($event_docs ?? [] as $doc)
            <a href="{{ \Storage::disk($doc->disk)->url(implode('/', array_filter([$doc->directory, $doc->path], 'filled'))) }}">Doc</a>
        @endforeach
        ```
        Or
        ```php
        @propertyArray('event', 'docs')
        @foreach ($event_docs ?? [] as $doc)
            <a href="{{ $doc }}">Doc</a>
        @endforeach
        ```
    </details>
</details>

<details>
    <summary><h3>Image</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Disk: The storage disk where images are saved.</li>
            <li>Directory: The directory path within the disk.</li>
            <li>Visibility: The visibility of the uploaded images.</li>
            <li>Multiple: Whether multiple images can be uploaded.</li>
            <li>Rule: Validation rules for the field.</li>
            <li>Accepted File Types: The allowed file types for upload.</li>
            <li>Min File: The minimum number of images required.</li>
            <li>Max File: The maximum number of images allowed.</li>
            <li>Min Size: The minimum image size allowed.</li>
            <li>Max Size: The maximum image size allowed.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @propertyArray('event', 'images')
        @foreach ($event_images ?? [] as $img)
            <img src="{{ \Storage::disk($img->disk)->url(implode('/', array_filter([$img->directory, $img->path], 'filled'))) }}" alt="Event Image">
        @endforeach
        ```
        Or
        ```php
        @propertyArray('event', 'images')
        @foreach ($event_images ?? [] as $img)
            <img src="{{ $img }}" alt="Event Image">
        @endforeach
        ```
    </details>
</details>

<details>
    <summary><h3>Color Picker</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Default Value: The initial value of the field.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @propertyNotEmpty('user', 'fav_color')
        <p style="color: {{ $user_fav_color }}">$user_fav_color</p>
        @endif
        ```
    </details>
</details>

<details>
    <summary><h3>DateTime Picker</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Default Value: The initial value of the field.</li>
            <li>Placeholder: Text displayed inside the field when empty.</li>
            <li>Prefix Label: Text displayed before the field.</li>
            <li>Suffix Label: Text displayed after the field.</li>
            <li>Has Time: Whether the field includes time selection.</li>
            <li>Has Date: Whether the field includes date selection.</li>
            <li>Rule: Validation rules for the field.</li>
            <li>Format: The format of the date/time value.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @propertyNotEmpty('event', 'date')
        <p>Year: {{ $event_date?->format('Y') }}</p>
        @endif
        ```
    </details>
</details>

<details>
    <summary><h3>Content Picker</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Document Type: The type of document to be linked.</li>
            <li>Start Node: The starting point for content selection.</li>
            <li>Min: The minimum number of items required.</li>
            <li>Max: The maximum number of items allowed.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
        @propertyNotEmpty('featured_blogs', 'blogs')
            @foreach ($featured_blogs_blogs as $featuredBlog)
                @php
                    $featuredBlogTemplate = $featuredBlog instanceof \SolutionForest\InspireCms\Dtos\ContentDto ? $featuredBlog?->getTemplate('blog-featured-item') : null;
                @endphp
                @if ($featuredBlogTemplate)
                    {!! $featuredBlogTemplate->render(['content' => $featuredBlog, 'locale' => $locale]) !!}
                @endif
            @endforeach
        @endif
        ```
    </details>
</details>

<details>
    <summary><h3>Media Picker</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Types: The types of media files allowed.</li>
            <li>Min: The minimum number of items required.</li>
            <li>Max: The maximum number of items allowed.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
            @propertyArray('hero', 'image_slider')
            @foreach ($hero_image_slider ?? [] as $item)
                <div class="swiper-slide">
                    <img src="{{ $item?->getUrl() }}" alt="Slide {{ $loop->iteration }}">
                    <p>{{ $item?->description }}</p>
                </div>
            @endforeach
        ```
    </details>
</details>

<details>
    <summary><h3>Repeater</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Fields: The sub-fields included in the repeater.</li>
            <li>Collapsible: Whether the repeater sections can be collapsed.</li>
            <li>Cloneable: Whether the repeater sections can be cloned.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
            @propertyArray('document_content', 'sections')
            @foreach ($document_content_sections ?? [] as $item)
                <section>
                    <h1>{{ $item->getPropertyData('title')?->getValue() }}</h1>
                    <p>{{ $item->getPropertyData('description')?->getValue() }}</p>
                    {{ $item->getPropertyData('content')?->getValue() ?? '' }}
                </section>
            @endforeach
        ```
    </details>
</details>

<details>
    <summary><h3>Tags</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Prefix Label: Text displayed before the field.</li>
            <li>Suffix Label: Text displayed after the field.</li>
            <li>Prefix: Text added before each tag.</li>
            <li>Suffix: Text added after each tag.</li>
            <li>Separator: The character used to separate tags.</li>
            <li>Suggestions: Predefined suggestions for tags.</li>
            <li>Reorderable: Whether tags can be reordered.</li>
            <li>Color: The color of the tags.</li>
            <li>Rule: Validation rules for the field.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
            @propertyArray('event', 'categories')
            <p>{{ implode(' | ', $event_categories ?? []) }}</p>
        ```
    </details>
</details>

<details>
    <summary><h3>Rich Editor</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Toolbar Buttons: The buttons available in the editor toolbar.</li>
            <li>Disk: The storage disk where files are saved.</li>
            <li>Directory: The directory path within the disk.</li>
            <li>Visibility: The visibility of the uploaded files.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
            @property('event', 'content')
        ```
    </details>
</details>

<details>
    <summary><h3>Markdown Editor</h3></summary>
    <details>
        <summary><h4>Configuration Options</h4></summary>
        <ul>
            <li>Translatable: Whether the field supports multiple languages.</li>
            <li>Toolbar Buttons: The buttons available in the editor toolbar.</li>
            <li>Disk: The storage disk where files are saved.</li>
            <li>Directory: The directory path within the disk.</li>
            <li>Visibility: The visibility of the uploaded files.</li>
        </ul>
    </details>
    <details>
        <summary><h4>Template Usage</h4></summary>
        ```php
            @property('document', 'content')
        ```
    </details>
</details>

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