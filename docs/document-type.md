---
title: Document Type
slug: document-type
path: docs/v1/document-type
uri: /docs/1.x/document-type
heading: Document Type
brief: Document Types in InspireCMS define the structure and behavior of your content. They determine what fields are available, how content is organized, and how it's presented to visitors.
---

## Overview

A document type is essentially a **blueprint** for content, similar to a class in object-oriented programming. It defines:

-   What fields are available for content entry
-   How content is organized in the admin interface
-   What templates can be used for rendering

---

## Creating Document Types

To create a document type:

1. Navigate to **Settings** > **Document Types** in the admin panel
2. Click **Create Document Type**
3. Configure the following settings:

### Basic Settings

-   **Name**: Human-readable name (e.g., "Blog Post")
-   **Slug**: Machine-readable identifier (e.g., "blog-post")
-   **Icon**: Visual identifier in the admin interface
-   **Category**: For organizing document types (e.g., "web", "data")

### Advanced Settings

-   **Show as Table**: Whether to display content in a table view
-   **Show at Root**: Whether content can be created at the root level
-   **Allowed document types**: Document types that can be children of this type

---

## Custom Fields

Associate custom fields with your document type to define what data can be entered:

1. In the **Structure** section, click **New field** or **Attach**
2. Select from available field groups or create new ones
3. Arrange field groups in the desired order

---

## Templates

Define which templates are available for rendering your content:

1. In the **Templates** section, click **Add Template** or **Attach**
2. Create a new template or select an existing one
3. Set a default template for new content

---

## Content Hierarchy

InspireCMS allows you to create parent-child relationships between content:

### Setting Child Types

Define which document types can be children of the current type:

1. In the **Allowed document types** field, select appropriate document types
2. This determines what content types can be created underneath this content
3. Leave empty to prevent child content from being created

---

## Using Document Types

Once document types are configured, content editors can use them to create structured content:

1. Navigate to the **Content** section in the admin panel
2. Click **Create Content** and select your document type
3. Fill in the fields defined by your field groups
4. Save and publish your content

---

## Best Practices

-   Create document types that align with your content strategy
-   Use clear, descriptive names for document types
-   Group related fields for better organization
-   Define parent-child relationships carefully to create logical content hierarchies
