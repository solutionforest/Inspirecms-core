# Export

InspireCMS provides powerful export capabilities for migrating content, creating backups, and transferring data between systems. This guide explains how to use the export features effectively.

## Export System Overview

The export system in InspireCMS allows you to:

- Export content and configuration in various formats
- Select specific elements to include in exports
- Schedule automatic exports
- Create data backups
- Prepare content for migration to other systems

## Export Interface

Access the export interface through:

```
Admin Panel → Settings → Export
```

### Export Types

InspireCMS supports several export types:

1. **Full Site Export**: All content, settings, and configuration
2. **Content Export**: Selected content items and their related data
3. **Document Type Export**: Document types with field groups and templates
4. **Field Group Export**: Field groups and their field definitions
5. **Template Export**: Templates and associated configuration
6. **User Export**: User accounts and roles
7. **Navigation Export**: Navigation structures

### Export Formats

Available export formats include:

- **JSON**: Complete structured data (default)
- **CSV**: Tabular data for spreadsheet manipulation
- **XML**: Structured data for systems requiring XML
- **YAML**: Human-readable structured data
- **SQL**: Direct database export for full backups

## Creating an Export

### Basic Export

To create a basic content export:

1. Go to **Settings → Export**
2. Click "Create Export"
3. Fill in the form:
   - **Name**: Descriptive name for the export
   - **Type**: Select export type (e.g., "Content Export")
   - **Format**: Choose format (e.g., JSON)
4. Configure additional options based on export type
5. Click "Create Export"

### Export Configuration Options

Depending on the export type, additional options may include:

- **Content Selection**: Which content items to include
- **Include Dependencies**: Whether to include related records
- **Language Options**: Which languages to export
- **Include Media**: Whether to include related media files
- **File Storage**: Where to store the export file
- **Compression**: Whether to compress the export file

### Content Selection

For content exports, select items to include:

1. Browse the content tree
2. Check boxes for content to include
3. Use filters to find specific content:
   - Filter by document type
   - Filter by status
   - Filter by creation date
   - Filter by author

### Media Handling

Configure how media files are handled:

1. **Include Media Files**: Yes/No
2. **Media Export Mode**:
   - **Reference Only**: Export only references to media
   - **Include Files**: Include actual media files in the export
   - **External URLs**: Replace media references with public URLs

## Executing Exports

### Immediate Execution

For small to medium exports:

1. Create the export configuration
2. Click "Export Now"
3. Wait for the export to complete
4. Download the export file

### Background Execution

For larger exports:

1. Create the export configuration
2. Click "Schedule Export"
3. The system processes the export in the background
4. Receive notification when export is complete
5. Download the export file from the export history

### Scheduled Exports

For regular backups or data syncing:

1. Create the export configuration
2. Enable "Recurring Export"
3. Set the schedule (daily, weekly, monthly)
4. Configure retention settings (how many exports to keep)

## Programmatic Exports

Create exports programmatically:

```php
use SolutionForest\InspireCms\Models\Export;
use SolutionForest\InspireCms\Services\ExportServiceInterface;

// Create export record
$export = new Export();
$export->name = 'Programmatic Export';
$export->type = 'content';
$export->format = 'json';
$export->options = [
    'content_ids' => ['550e8400-e29b-41d4-a716-446655440000', '550e8400-e29b-41d4-a716-446655440001'],
    'include_media' => true,
];
$export->save();

// Execute export
app(ExportServiceInterface::class)->execute($export);

// Get result
$exportFile = $export->getExportFilePath();
```

## Custom Exporters

InspireCMS allows you to create custom exporters:

```php
namespace App\Exports;

use SolutionForest\InspireCms\Exports\Exporters\BaseExporter;
use SolutionForest\InspireCms\Models\Contracts\Export;

class CustomExporter extends BaseExporter
{
    public function export(Export $record): ?string
    {
        // Implement your custom export logic
        $data = $this->collectData();
        
        // Generate export file
        $path = storage_path('app/exports/' . uniqid('export_') . '.json');
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        
        return $path;
    }
    
    protected function collectData(): array
    {
        // Logic to collect data for export
        return [
            // Your export data structure
        ];
    }
}
```

Register your custom exporter:

```php
// config/inspirecms.php
'exports' => [
    'exporters' => [
        \SolutionForest\InspireCms\Exports\Exporters\ContentExporter::class,
        \SolutionForest\InspireCms\Exports\Exporters\DocumentTypeExporter::class,
        // Add your custom exporter
        \App\Exports\CustomExporter::class,
    ],
],
```

## Security Considerations

Export files may contain sensitive information:

- Exports are only accessible to users with appropriate permissions
- Password-protect sensitive exports
- Consider encrypting export files that contain user data
- Limit which users can create and download exports

## Export Performance

For large sites, consider these performance optimizations:

1. **Chunked Exports**: Split large exports into manageable chunks
2. **Off-peak Scheduling**: Schedule large exports during off-peak hours
3. **Selective Exports**: Export only what's needed rather than everything
4. **Resource Allocation**: Increase PHP memory limits for large exports

```php
// For very large exports, set in your .env
EXPORT_MEMORY_LIMIT=2G
EXPORT_TIMEOUT=3600
```

## Export File Storage

Configure where export files are stored:

```php
// config/inspirecms.php
'exports' => [
    'disk' => 'local', // or 's3', 'sftp', etc.
    'directory' => 'exports',
    'retention_days' => 30, // Automatically delete files after 30 days
],
```

For cloud storage:

```php
// config/filesystems.php
'disks' => [
    // Local disk
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'throw' => false,
    ],
    
    // S3 for export storage
    'export_s3' => [
        'driver' => 's3',
        'key' => env('EXPORT_AWS_ACCESS_KEY_ID'),
        'secret' => env('EXPORT_AWS_SECRET_ACCESS_KEY'),
        'region' => env('EXPORT_AWS_DEFAULT_REGION'),
        'bucket' => env('EXPORT_AWS_BUCKET'),
        'url' => env('EXPORT_AWS_URL'),
    ],
],
```

## Export Best Practices

1. **Regular Backups**: Schedule regular exports for backup purposes
2. **Version Control**: Include version information in export files
3. **Documentation**: Document what's included in each export type
4. **Testing**: Test export and import processes in a staging environment
5. **Selective Exports**: Export only what you need for better performance
6. **Storage Management**: Implement retention policies to avoid accumulating old exports
7. **Security**: Secure export files that contain sensitive information
8. **Validation**: Verify export data integrity before distribution or import