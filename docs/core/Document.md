# Document Types

Document Types in InspireCMS define the structure and behavior of your content. They determine what fields are available, how content is organized, and how it's presented to visitors.

## Understanding Document Types

A document type is essentially a blueprint for content, similar to a class in object-oriented programming. It defines:

- What fields are available for content entry
- How content is organized in the admin interface
- What templates can be used for rendering
- How content is structured in the URL hierarchy

## Creating Document Types

To create a document type:

1. Navigate to **Settings** > **Document Types** in the admin panel
2. Click **Create Document Type**
3. Configure the following settings:

### Basic Settings

- **Name**: Human-readable name (e.g., "Blog Post")
- **Slug**: Machine-readable identifier (e.g., "blog-post")
- **Icon**: Visual identifier in the admin interface
- **Category**: For organizing document types (e.g., "Content", "Marketing")
- **Description**: Help text explaining the document type's purpose

### Advanced Settings

- **Show as Table**: Whether to display content in a table view
- **Show at Root**: Whether content can be created at the root level
- **Parent Types**: Document types that can be parents of this type
- **Child Types**: Document types that can be children of this type
- **Web Page**: Whether content renders as web pages with URLs
- **Route Pattern**: URL pattern for content (e.g., `/blog/{slug}`)

## Field Groups

Associate field groups with your document type to define what data can be entered:

1. In the **Field Groups** section, click **Add Field Group**
2. Select from available field groups or create new ones
3. Arrange field groups in the desired order

## Templates

Define which templates are available for rendering your content:

1. In the **Templates** section, click **Add Template**
2. Create a new template or select an existing one
3. Set a default template for new content

## Content Hierarchy

InspireCMS allows you to create parent-child relationships between content:

### Setting Parent Types

Define which document types can be parents of the current type:

1. In the **Parent Types** field, select appropriate document types
2. This controls where content can be placed in the content tree

### Setting Child Types

Define which document types can be children of the current type:

1. In the **Child Types** field, select appropriate document types
2. This determines what content types can be created underneath this content
3. Leave empty to prevent child content from being created

## Using Document Types

Once document types are configured, content editors can use them to create structured content:

1. Navigate to the **Content** section in the admin panel
2. Click **Create Content** and select your document type
3. Fill in the fields defined by your field groups
4. Save and publish your content

## Managing Document Types

Over time, you may need to modify your document types:

### Editing Document Types

1. Navigate to **Settings** > **Document Types**
2. Click on the document type you want to modify
3. Make changes to settings, field groups, or templates
4. Click **Save** to apply changes

### Deleting Document Types

Be cautious when deleting document types, as it may affect existing content:

1. Navigate to **Settings** > **Document Types**
2. Select the document type you want to remove
3. Click **Delete** and confirm your choice

## Importing and Exporting Document Types

You can share document types between InspireCMS installations:

1. Navigate to **Settings** > **Document Types**
2. Use the **Export** button to download document type definitions
3. On another installation, use the **Import** button to upload definitions

## Best Practices

- Create document types that align with your content strategy
- Use clear, descriptive names for document types
- Group related fields for better organization
- Define parent-child relationships carefully to create logical content hierarchies
- Test your document types with sample content before full implementation

## Next Steps

After setting up your document types, you'll want to:

1. [Create field groups](./FieldGroups.md) to define your content structure
2. [Design templates](./Templates.md) to render your content
3. [Set up navigation](./Navigation.md) to help users find your content