# Custom Fields

InspireCMS provides a flexible system for defining and managing fields in your content. Fields allow you to structure and organize data for templates, pages, and other content types.

## Field Types

Below is a list of available field types and their configurations:

### 1. **Text**
- **Description**: A single-line text input.
- **Configuration Options**:
  - `placeholder`: Placeholder text for the input.
  - `max_length`: Maximum number of characters allowed.
  - `default`: Default value for the field.

### 2. **Textarea**
- **Description**: A multi-line text input.
- **Configuration Options**:
  - `placeholder`: Placeholder text for the input.
  - `rows`: Number of rows to display.
  - `default`: Default value for the field.

### 3. **Rich Editor**
- **Description**: A WYSIWYG editor for rich text content.
- **Configuration Options**:
  - `toolbar`: Define the toolbar options.
  - `default`: Default content for the editor.

### 4. **Markdown Editor**
- **Description**: A markdown editor for writing content in markdown syntax.
- **Configuration Options**:
  - `default`: Default markdown content.

### 5. **Repeater**
- **Description**: A field for repeating groups of fields.
- **Configuration Options**:
  - `fields`: Define the fields within the repeater.
  - `min_items`: Minimum number of items allowed.
  - `max_items`: Maximum number of items allowed.

### 6. **Content Picker**
- **Description**: A field for selecting content items.
- **Configuration Options**:
  - `content_types`: Specify the types of content that can be selected.
  - `multiple`: Allow selecting multiple items.

### 7. **Media Picker**
- **Description**: A field for selecting media assets.
- **Configuration Options**:
  - `disk`: Specify the storage disk.
  - `directory`: Specify the directory for media files.
  - `multiple`: Allow selecting multiple media files.

### 8. **File**
- **Description**: A field for uploading files.
- **Configuration Options**:
  - `disk`: Specify the storage disk.
  - `directory`: Specify the directory for uploaded files.
  - `multiple`: Allow uploading multiple files.

### 9. **Date/Time**
- **Description**: A field for selecting dates and times.
- **Configuration Options**:
  - `format`: Specify the date/time format.
  - `default`: Default date/time value.

### 10. **Tags**
- **Description**: A field for adding tags.
- **Configuration Options**:
  - `separator`: Define the separator for tags.
  - `default`: Default tags.

### 11. **Checkbox**
- **Description**: A field for toggling a boolean value.
- **Configuration Options**:
  - `default`: Default value (`true` or `false`).

### 12. **Radio**
- **Description**: A field for selecting a single option from a list.
- **Configuration Options**:
  - `options`: Define the available options as key-value pairs.
  - `default`: Default selected option.

### 13. **Select**
- **Description**: A dropdown field for selecting a single or multiple options.
- **Configuration Options**:
  - `options`: Define the available options as key-value pairs.
  - `multiple`: Allow selecting multiple options.
  - `default`: Default selected option(s).

### 14. **Number**
- **Description**: A field for entering numeric values.
- **Configuration Options**:
  - `min`: Minimum value allowed.
  - `max`: Maximum value allowed.
  - `step`: Step size for incrementing/decrementing.
  - `default`: Default numeric value.

### 15. **Color Picker**
- **Description**: A field for selecting a color.
- **Configuration Options**:
  - `default`: Default color value.

### 16. **Toggle**
- **Description**: A switch-style field for toggling a boolean value.
- **Configuration Options**:
  - `default`: Default value (`true` or `false`).

### 17. **Slider**
- **Description**: A field for selecting a numeric value within a range using a slider.
- **Configuration Options**:
  - `min`: Minimum value.
  - `max`: Maximum value.
  - `step`: Step size for the slider.
  - `default`: Default slider value.

### 18. **Date Picker**
- **Description**: A field for selecting a date.
- **Configuration Options**:
  - `format`: Specify the date format.
  - `default`: Default date value.

### 19. **Time Picker**
- **Description**: A field for selecting a time.
- **Configuration Options**:
  - `format`: Specify the time format.
  - `default`: Default time value.

### 20. **DateTime Picker**
- **Description**: A field for selecting both date and time.
- **Configuration Options**:
  - `format`: Specify the date and time format.
  - `default`: Default date and time value.

### 21. **Range**
- **Description**: A field for selecting a numeric range.
- **Configuration Options**:
  - `min`: Minimum value.
  - `max`: Maximum value.
  - `step`: Step size for the range.
  - `default`: Default range value.

## Field Configuration

Each field type can be configured using the following common options:
- **Label**: The display name of the field.
- **Name**: The unique identifier for the field.
- **Validation Rules**: Define validation rules for the field (e.g., `required`, `max:255`).
- **Translatable**: Specify if the field supports multiple locales.
- **Default Value**: Set a default value for the field.

## Example Field Group Configuration

```php
[
    'name' => 'hero',
    'label' => 'Hero Section',
    'fields' => [
        [
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
            'validation' => 'required|max:255',
        ],
        [
            'name' => 'subtitle',
            'label' => 'Subtitle',
            'type' => 'textarea',
            'validation' => 'nullable|max:500',
        ],
        [
            'name' => 'image',
            'label' => 'Image',
            'type' => 'media_picker',
            'validation' => 'required',
        ],
    ],
];
```

## Using Fields in Templates

Fields can be accessed in templates using the `@property` directives. For more details, refer to the [Templating documentation](./Templating.md).

```php
<h1>@property('hero', 'title')</h1>
<p>@property('hero', 'subtitle')</p>
<img src="@property('hero', 'image')" alt="Hero Image">
```

## Additional Resources

- [Templating Documentation](./Templating.md)