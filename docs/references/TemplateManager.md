# Template Manager

The Template Manager in InspireCMS provides a set of tools to manage themes and templates. It allows you to handle theme-related operations such as retrieving the current theme, managing available themes, and exporting templates.

---

## Key Features

- Retrieve and manage the current theme.
- List available themes.
- Check if a theme exists.
- Create or clone themes.
- Export templates for a specific theme.
- Customize the `TemplateManager` implementation.

---

## Customizing the Template Manager

You can customize the `TemplateManager` by binding your own implementation in your application's service provider. Use Laravel's service container to override the default implementation.

### Example
```php
use SolutionForest\InspireCms\Base\TemplateManagerInterface;

public function boot()
{
    $this->app->singleton(TemplateManagerInterface::class, fn () => new YourCustomTemplateManager());
}
```

In this example, replace `YourCustomTemplateManager` with your custom implementation of the `TemplateManagerInterface`.

---

## Accessing the Template Manager

You can access the `TemplateManager` using either of the following methods:

1. **Using the Facade**:
   ```php
   use \SolutionForest\InspireCms\Facades\Templates;

   $currentTheme = Templates::getCurrentTheme();
   ```

2. **Using the Helper Function**:
   ```php
   $currentTheme = inspirecms_templates()->getCurrentTheme();
   ```

Both approaches provide access to the same methods and functionality.

---

## Methods

### `getCurrentTheme()`
Retrieve the name of the current theme.

```php
$currentTheme = Templates::getCurrentTheme();
// or
$currentTheme = inspirecms_templates()->getCurrentTheme();

echo $currentTheme;
```

### `clearCurrentThemeCache()`
Clear the cache for the current theme.

```php
Templates::clearCurrentThemeCache();
// or
inspirecms_templates()->clearCurrentThemeCache();
```

### `resetCurrentTheme()`
Reset the current theme and clear its cache.

```php
Templates::resetCurrentTheme();
// or
inspirecms_templates()->resetCurrentTheme();
```

### `getAvailableThemes()`
Retrieve a list of all available themes.

```php
$availableThemes = Templates::getAvailableThemes();
// or
$availableThemes = inspirecms_templates()->getAvailableThemes();

print_r($availableThemes);
```

### `isThemeExists(string $theme)`
Check if a specific theme exists.

```php
$themeExists = Templates::isThemeExists('my-theme');
// or
$themeExists = inspirecms_templates()->isThemeExists('my-theme');

echo $themeExists ? 'Theme exists' : 'Theme does not exist';
```

### `getComponentWithTheme(string $componentName, ?string $theme = null)`
Retrieve the component name with the theme prefix.

```php
$component = Templates::getComponentWithTheme('header', 'my-theme');
// or
$component = inspirecms_templates()->getComponentWithTheme('header', 'my-theme');

echo $component;
```

### `getComponentPathWithTheme(?string $componentName = null, ?string $theme = null)`
Retrieve the file path for a component within a theme.

```php
$path = Templates::getComponentPathWithTheme('header', 'my-theme');
// or
$path = inspirecms_templates()->getComponentPathWithTheme('header', 'my-theme');

echo $path;
```

### `getThemeDefaultLayoutPath(?string $theme = null)`
Retrieve the file path for the default layout of a theme.

```php
$layoutPath = Templates::getThemeDefaultLayoutPath('my-theme');
// or
$layoutPath = inspirecms_templates()->getThemeDefaultLayoutPath('my-theme');

echo $layoutPath;
```

### `createTheme(string $theme)`
Create a new theme with the default layout.

```php
$success = Templates::createTheme('new-theme');
// or
$success = inspirecms_templates()->createTheme('new-theme');

echo $success ? 'Theme created successfully' : 'Failed to create theme';
```

### `cloneTheme(string $sourceTheme, string $newTheme)`
Clone an existing theme to create a new one.

```php
$success = Templates::cloneTheme('source-theme', 'cloned-theme');
// or
$success = inspirecms_templates()->cloneTheme('source-theme', 'cloned-theme');

echo $success ? 'Theme cloned successfully' : 'Failed to clone theme';
```

### `assignDefaultTemplateIfNotSet($templateable, $template)`
Assign a default template to a model if it is not already set.

```php
$templateable = app(\SolutionForest\InspireCms\Models\Contracts\HasTemplates::class)::find(1);
$template = 'default-template';

Templates::assignDefaultTemplateIfNotSet($templateable, $template);
// or
inspirecms_templates()->assignDefaultTemplateIfNotSet($templateable, $template);
```

### `exportTemplate($template, ?string $theme = null)`
Export a template for a specific theme to a file.

```php
$template = app(\SolutionForest\InspireCms\Models\Contracts\Template::class)::find(1);

Templates::exportTemplate($template, 'my-theme');
// or
inspirecms_templates()->exportTemplate($template, 'my-theme');
```

---

## Example Usage

### Creating and Managing Themes
```php
// Using the Facade
Templates::createTheme('new-theme');
Templates::cloneTheme('default-theme', 'custom-theme');
$themes = Templates::getAvailableThemes();

// Using the Helper Function
inspirecms_templates()->createTheme('new-theme');
inspirecms_templates()->cloneTheme('default-theme', 'custom-theme');
$themes = inspirecms_templates()->getAvailableThemes();

print_r($themes);
```

### Exporting Templates
```php
$template = app(\SolutionForest\InspireCms\Models\Contracts\Template::class)::find(1);

// Using the Facade
Templates::exportTemplate($template, 'custom-theme');

// Using the Helper Function
inspirecms_templates()->exportTemplate($template, 'custom-theme');
```

---

## Notes
- The Template Manager relies on helper classes like `TemplateHelper` and `FileHelper` for file operations.
- Ensure that the directory structure for themes and templates is properly configured in your application.

For more details, refer to the [TemplateManagerInterface](/src/Base/TemplateManagerInterface.php).
