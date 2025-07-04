---
title: Export
slug: export
path: docs/v1/export
uri: /docs/v1/export
heading: Export
brief: InspireCMS provides powerful export capabilities for migrating content, creating backups, and transferring data between systems. This guide explains how to use the export features effectively.

quick_links: []
---


## Overview

The export system in InspireCMS allows you to:

- Export content and configuration in various formats
- Select specific elements to include in exports
- Schedule automatic exports
- Create data backups
- Prepare content for migration to other systems

---

## Export Interface

Access the export interface through: **Settings** > **Export**

![Setting_export](https://inspirecms.net/storage/doc/e29gUHKw0P2KHrHdqFIbohIj7QX3apS6rieNEnkt.png)


### Export Types

InspireCMS supports several export types:

1. **Full Site Export**: All content, settings, and configuration
2. **Document Type Export**: Document types with field groups and templates
3. **Field Group Export**: Field groups and their field definitions
4. **Template Export**: Templates and associated configuration

### Export Formats

Available export formats include:

- **JSON**: Complete structured data (default)

---

## Creating an Export

### Basic Export

To create a basic content export:

1. Go to **Settings** > **Export**
2. Click "**Export**" to create new export
3. Configure additional options based on export type
4. Click "**Export**" to submit request
5. Wait for the export to complete or execute command `php artisan inspirecms:export`
6. Download the export file

### Export Configuration Options

Depending on the export type, additional options may include:

- **Content Selection**: Which content items to include
- **Include Dependencies**: Whether to include related records

---

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

```php {title="config/inspirecms.php"}
'import_export' => [
    'exports' => [
        'exporters' => [
            \SolutionForest\InspireCms\Exports\Exporters\ContentExporter::class,
            \SolutionForest\InspireCms\Exports\Exporters\DocumentTypeExporter::class,
            // Add your custom exporter
            \App\Exports\CustomExporter::class,
        ],
    ],
],
```

---

## Export File Storage

Configure where export files are stored:

```php {title="config/inspirecms.php"}
'models' => [
    'prunable' => [
        'export' => [
            'interval' => 5, // Automatically delete files after 5 days
        ],
    ],
],
'import_export' => [
    'exports' => [
        'disk' => 'local', // or 's3', 'sftp', etc.
        'directory' => 'exports',
    ],
],
```

For cloud storage:

```php {title="config/filesystems.php"}
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