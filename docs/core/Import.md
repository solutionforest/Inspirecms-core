# Import

InspireCMS provides comprehensive import capabilities for migrating content, restoring backups, and bringing data from other systems. This guide explains how to use the import features effectively.

## Import System Overview

The import system in InspireCMS allows you to:

- Import content from various formats and sources
- Migrate from other CMS platforms
- Restore data from backups
- Bring in content from external systems
- Create content in bulk
- Update existing content

## Import Interface

Access the import interface through:

```
Admin Panel → Settings → Import
```

### Import Types { .font-bold  .text-2xl .my-2 }

InspireCMS supports several import types:

1. **Full Site Import**: Complete site data including content and settings
2. **Content Import**: Content items and their properties
3. **Document Type Import**: Document types with field groups and templates
4. **Field Group Import**: Field group definitions
5. **Template Import**: Templates and their configuration
6. **User Import**: User accounts and their roles
7. **Media Import**: Media library assets

### Supported Formats { .font-bold  .text-2xl .my-2 }

Import data can be in various formats:

- **JSON**: Structured data (preferred for full imports)
- **CSV**: Tabular data for simple content imports
- **XML**: Structured data from other systems
- **YAML**: Human-readable structured data
- **SQL**: Database dumps for full restoration

## Creating an Import

### Basic Import { .font-bold  .text-2xl .my-2 }

To create a basic import:

1. Go to **Settings → Import**
2. Click "Create Import"
3. Fill in the form:
   - **Name**: Descriptive name for the import
   - **Type**: Select import type (e.g., "Content Import")
   - **Format**: Choose format (e.g., JSON)
4. Upload the import file or provide a URL
5. Configure additional options based on import type
6. Click "Create Import"

### Import Configuration Options { .font-bold  .text-2xl .my-2 }

Depending on the import type, additional options may include:

- **Update Strategy**: How to handle existing records (update, skip, duplicate)
- **Language Mapping**: How to map imported languages to system languages
- **User Mapping**: How to assign content ownership
- **Media Handling**: How to process media references
- **Validation Rules**: Custom rules for validating imported data
- **Notifications**: Who to notify when import completes

### File Upload { .font-bold  .text-2xl .my-2 }

Upload import files directly:

1. Prepare your import file in the appropriate format
2. Select "File Upload" as the source
3. Click "Choose File" and select your import file
4. Set a maximum file size in your configuration:

```php
// config/inspirecms.php
'imports' => [
    'max_file_size' => 50 * 1024, // 50MB in KB
],
```

### URL Import { .font-bold  .text-2xl .my-2 }

Import from a remote URL:

1. Select "URL" as the source
2. Enter the URL of the import file
3. Optional: Provide authentication credentials if required
4. The system will download the file before processing

### Manual Data Entry { .font-bold  .text-2xl .my-2 }

For simple imports, use manual data entry:

1. Select "Manual Entry" as the source
2. Use the provided interface to enter data
3. Suitable for small imports where you're adding a few items

## Executing Imports

### Validation { .font-bold  .text-2xl .my-2 }

Before executing, imports are validated:

1. File format validation
2. Schema validation
3. Data integrity checks
4. Reference validation
5. Custom validation rules

Failed validation shows errors to help you fix the import file.

### Dry Run { .font-bold  .text-2xl .my-2 }

Test imports without making changes:

1. Create the import configuration
2. Enable "Dry Run Mode"
3. Click "Start Import"
4. Review the results to check for potential issues
5. If everything looks good, disable "Dry Run Mode" and run the import

### Immediate Execution { .font-bold  .text-2xl .my-2 }

For small to medium imports:

1. Create the import configuration
2. Click "Import Now"
3. Wait for the import to complete
4. Review the import results

### Background Execution { .font-bold  .text-2xl .my-2 }

For larger imports:

1. Create the import configuration
2. Click "Schedule Import"
3. The system processes the import in the background
4. Receive notification when import is complete
5. Review the import results from the import history

## Programmatic Imports

Create imports programmatically:

```php
use SolutionForest\InspireCms\Models\Import;
use SolutionForest\InspireCms\Services\ImportServiceInterface;

// Create import record
$import = new Import();
$import->name = 'Programmatic Import';
$import->type = 'content';
$import->format = 'json';
$import->options = [
    'update_strategy' => 'update',
    'validate_only' => false,
];
$import->save();

// Set the import file
$import->attachFile(storage_path('app/imports/content-data.json'));

// Execute import
app(ImportServiceInterface::class)->execute($import);

// Check results
$results = $import->results;
```

## Import Data Service

For more control, use the Import Data Service to define and perform imports:

```php
use SolutionForest\InspireCms\ImportData\Entities;
use SolutionForest\InspireCms\Services\ImportDataServiceInterface;

$service = app(ImportDataServiceInterface::class);

// Define document type
$service->addDocumentType('blog', new Entities\DocumentType(
    slug: 'blog',
    showAsTable: true,
    showAtRoot: true,
    category: 'content',
    icon: 'heroicon-o-document-text',
    fieldGroups: ['blog_content'],
    templates: ['blog_post'],
    defaultTemplate: 'blog_post',
));

// Define field group
$service->addFieldGroup('blog_content', new Entities\FieldGroup(
    slug: 'blog_content',
    fields: [
        new Entities\Field(slug: 'title', type: 'text', config: ['translatable' => true]),
        new Entities\Field(slug: 'body', type: 'richEditor', config: ['translatable' => true]),
        new Entities\Field(slug: 'featured_image', type: 'mediaPicker', config: ['types' => ['image']]),
    ],
));

// Define template
$service->addTemplate('blog_post', '<h1>@property(\'blog_content\', \'title\')</h1>@property(\'blog_content\', \'body\')');

// Add content
$service->addContent('blog', null, new Entities\Content(
    slug: 'hello-world',
    title: ['en' => 'Hello World'],
    documentType: 'blog',
    properties: [
        'blog_content' => [
            'title' => ['en' => 'Hello World'],
            'body' => ['en' => '<p>This is my first blog post</p>'],
            'featured_image' => [],
        ],
    ],
    publishState: 'publish'
));

// Run the import
$service->run();
```

## Custom Importers

Create a custom importer for specialized needs:

```php
namespace App\Imports;

use SolutionForest\InspireCms\Imports\Importers\BaseImporter;
use SolutionForest\InspireCms\Models\Contracts\Import;

class CustomImporter extends BaseImporter
{
    public function import(Import $record): bool
    {
        // Get the import file
        $file = $record->getImportFilePath();
        
        if (!$file || !file_exists($file)) {
            $record->addError('File not found or inaccessible');
            return false;
        }
        
        // Read the file
        $data = file_get_contents($file);
        
        // Process the data
        try {
            // Your custom import logic
            $this->processImportData(json_decode($data, true));
            
            // Log success
            $record->addResult('Successfully imported custom data');
            return true;
        } catch (\Exception $e) {
            // Log failure
            $record->addError('Import failed: ' . $e->getMessage());
            return false;
        }
    }
    
    protected function processImportData(array $data): void
    {
        // Implement your custom processing logic
        foreach ($data as $item) {
            // Process each item
        }
    }
}
```

Register your custom importer:

```php
// config/inspirecms.php
'imports' => [
    'importers' => [
        'custom' => \App\Imports\CustomImporter::class,
    ],
],
```

## Import History

View and manage imports:

1. Go to **Settings → Import History**
2. See a list of all imports with:
   - Import name
   - Creation date
   - Status (pending, running, completed, failed)
   - Summary of results
3. Click an import to see detailed results

## Migration from Other Systems

InspireCMS provides tools for migrating from other CMS platforms:

### WordPress Migration { .font-bold  .text-2xl .my-2 }

Import content from WordPress:

1. Create a WordPress XML export
2. Go to **Settings → Import**
3. Create a new import with type "WordPress Import"
4. Upload the WordPress export file
5. Configure mapping options for categories, tags, and media
6. Execute the import

### Custom CMS Migration { .font-bold  .text-2xl .my-2 }

For custom migrations from other systems:

1. Create an intermediary format (usually JSON)
2. Map the external system's data structure to InspireCMS's structure
3. Use the Import Data Service for granular control
4. Consider writing a custom importer for complex migrations

## Error Handling and Rollback

### Handling Import Errors { .font-bold  .text-2xl .my-2 }

When errors occur during import:

1. The import process logs detailed error information
2. For immediate imports, errors are displayed on screen
3. For background imports, errors are stored in the import record
4. Review errors in the import details view

### Import Rollback { .font-bold  .text-2xl .my-2 }

For critical imports, use transaction support:

```php
// In a custom importer
public function import(Import $record): bool
{
    DB::beginTransaction();
    
    try {
        // Perform import operations
        
        // If everything succeeded
        DB::commit();
        return true;
    } catch (\Exception $e) {
        // If anything failed, roll back all changes
        DB::rollBack();
        $record->addError('Import failed and was rolled back: ' . $e->getMessage());
        return false;
    }
}
```

## Import Templates

Save import configurations as templates:

1. Create an import with your desired settings
2. Click "Save as Template" 
3. Give the template a name and description
4. The template appears in your templates list
5. Create new imports based on the template

## Import Best Practices

1. **Test First**: Always test imports in a staging environment
2. **Backup**: Create a backup before large imports
3. **Start Small**: Begin with a small sample to validate your import file
4. **Validate Data**: Clean and validate data before importing
5. **Use Dry Runs**: Test imports in dry run mode before committing changes
6. **Monitor Resources**: Large imports may require increased memory or timeout settings
7. **Log Results**: Keep detailed logs of import operations
8. **Handle Media**: Plan how media files will be handled during import
9. **Map Relations**: Carefully map relationships between imported items
10. **Check Consistency**: Verify data consistency after import