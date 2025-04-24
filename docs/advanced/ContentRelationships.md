# Content Relationships

InspireCMS provides powerful tools for creating and managing relationships between different content items. This guide explains how to define, create, and utilize content relationships effectively.

## Overview

Content relationships in InspireCMS allow you to:

- Create hierarchical content structures
- Reference related content items
- Build complex content models with interconnected pieces
- Create taxonomies and categorization systems
- Implement navigation structures

## Types of Relationships

InspireCMS supports several types of content relationships:

### 1. Hierarchical (Parent-Child) Relationships

Content items can be organized in a hierarchical tree structure:

```
Home Page
├── About Us
│   ├── Our Team
│   └── Our History
├── Services
│   ├── Service 1
│   ├── Service 2
│   └── Service 3
└── Contact
```

### 2. Reference Relationships

Content items can reference other content:

- Blog posts can reference authors
- Products can reference categories
- Pages can reference related content
- Events can reference speakers or venues

### 3. Taxonomy Relationships

Content can be organized using taxonomy systems:

- Categories and sub-categories
- Tags and keywords
- Product attributes
- Geographic locations

## Implementing Hierarchical Relationships

### Document Type Configuration

Hierarchical relationships are defined through document type configuration:

```php
// config/inspirecms.php
'document_types' => [
    'page' => [
        'slug' => 'page',
        'title' => 'Page',
        'allowed' => ['page'], // Pages can contain other pages
    ],
    'blog_category' => [
        'slug' => 'blog_category',
        'title' => 'Blog Category',
        'allowed' => ['blog_post'], // Categories can contain blog posts
    ],
    'blog_post' => [
        'slug' => 'blog_post',
        'title' => 'Blog Post',
        'allowed' => [], // Blog posts cannot contain other content
    ],
],
```

### Creating Parent-Child Content

Through the admin interface:

1. Navigate to the parent content item
2. Click "Add Child" or "Create New"
3. Select the document type for the child content
4. Create the child content

Via code:

```php
// Create a parent page
$parentPage = new \SolutionForest\InspireCms\Models\Content();
$parentPage->title = 'Parent Page';
$parentPage->document_type = 'page';
$parentPage->save();

// Create a child page
$childPage = new \SolutionForest\InspireCms\Models\Content();
$childPage->title = 'Child Page';
$childPage->document_type = 'page';
$childPage->parent()->associate($parentPage);
$childPage->save();
```

### Accessing Parent-Child Relationships

```php
// Get the parent of a content item
$parent = $content->parent;

// Get all children of a content item
$children = $content->children;

// Get all descendants (children, grandchildren, etc.)
$descendants = $content->descendants;

// Get all ancestors (parent, grandparent, etc.)
$ancestors = $content->ancestors;

// Check if content has children
if ($content->hasChildren()) {
    // Do something with children
}

// Get siblings (other content items with the same parent)
$siblings = $content->siblings;

// Get the next sibling (based on sort order)
$next = $content->nextSibling;

// Get the previous sibling (based on sort order)
$previous = $content->previousSibling;
```

## Implementing Reference Relationships

Reference relationships are created using the `contentPicker` field type.

### Creating a Reference Field

```php
// Field Group: Blog Post Details
[
    'slug' => 'post_details',
    'title' => 'Post Details',
    'fields' => [
        [
            'slug' => 'author',
            'label' => 'Author',
            'type' => 'contentPicker',
            'config' => [
                'documentTypes' => ['author'], // Only allow author document types
                'multiple' => false, // Single selection
            ],
        ],
        [
            'slug' => 'categories',
            'label' => 'Categories',
            'type' => 'contentPicker',
            'config' => [
                'documentTypes' => ['blog_category'], // Only allow blog category document types
                'multiple' => true, // Allow multiple selections
            ],
        ],
        [
            'slug' => 'related_posts',
            'label' => 'Related Posts',
            'type' => 'contentPicker',
            'config' => [
                'documentTypes' => ['blog_post'], // Only allow blog post document types
                'multiple' => true, // Allow multiple selections
                'exclude' => [
                    'self' => true, // Exclude this content item from selection
                ],
            ],
        ],
    ],
]
```

### Accessing Referenced Content

```php
// Get a single referenced content item
$author = $blogPost->getPropertyValue('post_details', 'author');
if ($author) {
    echo $author->getTitle();
}

// Get multiple referenced content items
$categories = $blogPost->getPropertyValue('post_details', 'categories', []);
foreach ($categories as $category) {
    echo $category->getTitle();
}

// Check if a reference exists
if ($blogPost->hasProperty('post_details', 'related_posts')) {
    $relatedPosts = $blogPost->getPropertyValue('post_details', 'related_posts', []);
    // Process related posts
}
```

### Accessing Content That References This Item

You can query content that references a specific item:

```php
// Find all blog posts that reference this author
$authorId = $author->id;
$blogPosts = inspirecms_content()
    ->getByDocumentType('blog_post')
    ->filter(function ($post) use ($authorId) {
        $postAuthor = $post->getPropertyValue('post_details', 'author');
        return $postAuthor && $postAuthor->id === $authorId;
    });

// This can also be done with a query, which is more efficient
$blogPosts = inspirecms_content()
    ->queryByDocumentType('blog_post')
    ->whereJsonContains('latest_data->properties->post_details->author', $authorId)
    ->get();
```

## Building Taxonomies

Taxonomies are hierarchical classification systems. In InspireCMS, taxonomies are implemented as content items.

### Creating a Taxonomy System

1. Create a document type for your taxonomy terms:

```php
// Document Type: Category
[
    'slug' => 'category',
    'title' => 'Category',
    'showAsTable' => false,
    'allowed' => ['category'], // Categories can contain sub-categories
]
```

2. Create a field group for your taxonomy terms:

```php
// Field Group: Category Details
[
    'slug' => 'category_details',
    'title' => 'Category Details',
    'fields' => [
        [
            'slug' => 'description',
            'label' => 'Description',
            'type' => 'textarea',
            'config' => [
                'translatable' => true,
            ],
        ],
    ],
]
```

3. Create a field for assigning taxonomy terms to content:

```php
// Field Group: Content Categorization
[
    'slug' => 'categorization',
    'title' => 'Categorization',
    'fields' => [
        [
            'slug' => 'categories',
            'label' => 'Categories',
            'type' => 'contentPicker',
            'config' => [
                'documentTypes' => ['category'],
                'multiple' => true,
            ],
        ],
        [
            'slug' => 'tags',
            'label' => 'Tags',
            'type' => 'tags',
            'config' => [
                'suggestions' => ['news', 'featured', 'promoted', 'trending'],
                'freeInput' => true, // Allow custom tags
            ],
        ],
    ],
]
```

### Using Taxonomies for Content Organization

```php
// Get all content in a specific category
$categoryId = $category->id;
$contentInCategory = inspirecms_content()
    ->queryAll()
    ->whereJsonContains('latest_data->properties->categorization->categories', $categoryId)
    ->get();

// Get content with a specific tag
$tagName = 'featured';
$featuredContent = inspirecms_content()
    ->queryAll()
    ->whereJsonContains('latest_data->properties->categorization->tags', $tagName)
    ->get();
```

## Many-to-Many Relationships

Many-to-many relationships can be implemented using the `contentPicker` field with `multiple` set to `true`.

### Example: Authors and Publications

```php
// Field Group: Author Publications
[
    'slug' => 'author_publications',
    'title' => 'Publications',
    'fields' => [
        [
            'slug' => 'publications',
            'label' => 'Publications',
            'type' => 'contentPicker',
            'config' => [
                'documentTypes' => ['publication'],
                'multiple' => true,
            ],
        ],
    ],
]

// Field Group: Publication Authors
[
    'slug' => 'publication_authors',
    'title' => 'Authors',
    'fields' => [
        [
            'slug' => 'authors',
            'label' => 'Authors',
            'type' => 'contentPicker',
            'config' => [
                'documentTypes' => ['author'],
                'multiple' => true,
            ],
        ],
    ],
]
```

## One-to-One Relationships

One-to-one relationships can be implemented using the `contentPicker` field with `multiple` set to `false`.

### Example: User Profile

```php
// Field Group: User Details
[
    'slug' => 'user_details',
    'title' => 'User Details',
    'fields' => [
        [
            'slug' => 'profile',
            'label' => 'Profile',
            'type' => 'contentPicker',
            'config' => [
                'documentTypes' => ['profile'],
                'multiple' => false,
            ],
        ],
    ],
]
```

## Bidirectional Relationships

You can create bidirectional relationships by setting up fields on both related content types.

### Example: Courses and Instructors

```php
// Field Group: Course Instructors
[
    'slug' => 'course_instructors',
    'title' => 'Instructors',
    'fields' => [
        [
            'slug' => 'instructors',
            'label' => 'Instructors',
            'type' => 'contentPicker',
            'config' => [
                'documentTypes' => ['instructor'],
                'multiple' => true,
            ],
        ],
    ],
]

// Field Group: Instructor Courses
[
    'slug' => 'instructor_courses',
    'title' => 'Courses',
    'fields' => [
        [
            'slug' => 'courses',
            'label' => 'Courses',
            'type' => 'contentPicker',
            'config' => [
                'documentTypes' => ['course'],
                'multiple' => true,
            ],
        ],
    ],
]
```

## Advanced Features

### Content Relationship Validators

You can add validation rules to ensure relationship integrity:

```php
// Field with validation rules
[
    'slug' => 'primary_category',
    'label' => 'Primary Category',
    'type' => 'contentPicker',
    'config' => [
        'documentTypes' => ['category'],
        'multiple' => false,
        'required' => true, // This relationship is required
    ],
]

// Limiting the number of relationships
[
    'slug' => 'featured_products',
    'label' => 'Featured Products',
    'type' => 'contentPicker',
    'config' => [
        'documentTypes' => ['product'],
        'multiple' => true,
        'min' => 2, // At least 2 products
        'max' => 5, // At most 5 products
    ],
]
```

### Relationship Sorting

Control the order of relationships:

```php
// Field with sorted relationships
[
    'slug' => 'gallery_images',
    'label' => 'Gallery Images',
    'type' => 'contentPicker',
    'config' => [
        'documentTypes' => ['media_asset'],
        'multiple' => true,
        'sortable' => true, // Allow manual sorting
    ],
]
```

### Relationship Filters

Filter available content for relationships:

```php
// Field with filtered options
[
    'slug' => 'featured_posts',
    'label' => 'Featured Posts',
    'type' => 'contentPicker',
    'config' => [
        'documentTypes' => ['blog_post'],
        'multiple' => true,
        'filters' => [
            // Only show published posts
            'published' => true,
            // Only show posts from the last 30 days
            'published_after' => '-30 days',
        ],
    ],
]
```

## Best Practices

1. **Plan Your Content Structure**: Design your content relationships before implementation
2. **Use Appropriate Relationship Types**: Choose the right type of relationship for each connection
3. **Avoid Circular References**: Be careful not to create circular dependency chains
4. **Document Relationships**: Create clear documentation about how content items relate
5. **Limit Relationship Depth**: Deep hierarchies can become difficult to manage
6. **Consider Performance**: Accessing deeply nested relationships can impact performance
7. **Be Consistent**: Use consistent patterns for similar relationship types
8. **Set Constraints**: Define clear rules for what relationships are allowed

## Troubleshooting

### Missing Related Content

If related content isn't appearing:

1. Check that the relationship field is properly configured
2. Verify that the related content exists and has the right document type
3. Ensure the relationship was properly saved in the content editor

### Relationship Performance Issues

If loading related content is slow:

1. Consider eager loading related content for better performance
2. Limit the depth of relationship queries
3. Cache frequently accessed relationship data
4. Use pagination when displaying large sets of related content

### Relationship Data Integrity

If relationship data becomes corrupted:

1. Check for deleted content that might be referenced
2. Ensure proper validation is in place for required relationships
3. Consider implementing cleanup tasks for orphaned relationships