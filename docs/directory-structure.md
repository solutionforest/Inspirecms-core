---
title: Directory Structure
slug: directory-structure
path: docs/v1/directory-structure
uri: /docs/v1/directory-structure
heading: Directory Structure
brief: Understanding the InspireCMS directory structure helps you efficiently develop and customize your application. This guide explains the key directories and files in an InspireCMS installation.
quick_links: []
---



## Overview

InspireCMS follows Laravel's standard directory structure, with additional directories and files specific to CMS functionality.

```plaintext
project/ 
├── app/ 
│   └── Filament 
│      └── Cms/ # Custom CMS extensions 
│          ├── Clusters/ # Custom admin panel clusters 
│          ├── Pages/ # Custom admin panel pages 
│          ├── Resources/ # Custom admin panel resources 
│          └── Widgets/ # Custom admin panel widgets 
├── config/ 
│   └── inspirecms.php # Main CMS configuration file 
└── resources/ 
    └── views/ 
        ├── components/ 
        │   └── inspirecms/ # CMS components and templates 
        │       └── {theme}/ # Theme-specific components 
        └── inspirecms/ # Exported CMS templates 
            └── templates/ 
```

---

## Key Directories Explained

### App Extensions

- **`app/Filament/Cms/`**: Directory for all your custom CMS extensions
  - **`Clusters/`**: Custom admin panel sections, each containing related resources
  - **`Pages/`**: Custom admin dashboard pages
  - **`Resources/`**: Custom Filament resources for CRUD operations
  - **`Widgets/`**: Custom dashboard widgets

### Configuration

- **`config/inspirecms.php`**: The main configuration file for InspireCMS, containing settings for:
  - Authentication and users
  - Media management
  - Custom models
  - Database connections
  - Frontend themes
  - Caching
  - Permission settings

### Resources

- **`resources/views/components/inspirecms/{theme}/`**: Theme-specific components
  - `page.blade.php`: Default page layout template
  - `layout.blade.php`: Base layout used by templates
  - Various component templates (header, footer, navigation, etc.)
- **`resources/views/inspirecms/templates/`**: Exported templates from the CMS

---

## Custom Theme Structure

When creating a new theme, follow this structure:

```plaintext
resources/views/components/inspirecms/{theme-name}/ 
├── page.blade.php # Default page layout template 
├── header.blade.php # Header component 
├── footer.blade.php # Footer component 
└── navigation.blade.php # Navigation component
```

---

## Adding Custom Extensions

### Creating a Custom Cluster

```bash
php artisan make:filament-cluster YourClusterName --panel=cms
```

### Creating a Custom Resource

```bash
php artisan make:filament-resource YourModel --panel=cms
```

### Creating a Custom Page

```bash
php artisan make:filament-page YourPage --panel=cms
```