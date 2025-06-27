---
title: Search Implementation
slug: search-implementation
path: docs/v1/search-implementation
uri: /docs/v1/search-implementation
heading: 
brief: 
quick_links: null
---

# Search Implementation

InspireCMS provides flexible search capabilities to help users find content quickly and efficiently. This guide covers how to implement, customize, and optimize search functionality in your InspireCMS application.

## Overview

The search system in InspireCMS allows you to:

- Search across all content types
- Filter results by document type, status, and other criteria
- Implement full-text search
- Customize search relevance and ranking
- Create advanced search interfaces

## Basic Search Implementation

### Using the Built-in Search

InspireCMS includes a basic search function that can be used out of the box:

```php
// Simple search across all content
$results = inspirecms_content()->search('keyword');

// Search with specific document type
$results = inspirecms_content()
    ->documentType('blog_post')
    ->search('keyword');

// Search with pagination
$results = inspirecms_content()
    ->search('keyword')
    ->paginate(10);
```

### Search Controller Example

Here's a simple search controller implementation:

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $documentType = $request->input('type');
        $page = $request->input('page', 1);
        
        if (empty($query)) {
            return view('search.index', [
                'query' => '',
                'results' => null,
            ]);
        }
        
        $searchBuilder = inspirecms_content()
            ->isPublished(true);
            
        if ($documentType) {
            $searchBuilder->documentType($documentType);
        }
        
        $results = $searchBuilder
            ->search($query)
            ->paginate(12);
            
        return view('search.index', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
```

### Search Template Example

Create a search results template:

```blade {title="resources/views/search/index.blade.php"}
<x-inspirecms-your-theme::layout>
    <div class="container">
        <h1>Search Results</h1>
        
        <form action="{{ route('search') }}" method="GET" class="search-form">
            <div class="input-group mb-4">
                <input 
                    type="text" 
                    name="q" 
                    value="{{ $query }}" 
                    class="form-control"
                    placeholder="Search..."
                    required
                >
                <select name="type" class="form-select">
                    <option value="">All Content</option>
                    <option value="page" {{ request('type') == 'page' ? 'selected' : '' }}>Pages</option>
                    <option value="blog_post" {{ request('type') == 'blog_post' ? 'selected' : '' }}>Blog Posts</option>
                    <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Products</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
        
        @if($query)
            <div class="search-results">
                <p>{{ $results->total() }} results found for "{{ $query }}"</p>
                
                @if($results->count() > 0)
                    <div class="results-list">
                        @foreach($results as $item)
                            <div class="result-item">
                                <h3>
                                    <a href="{{ $item->getUrl() }}">
                                        {{ $item->getTitle() }}
                                    </a>
                                </h3>
                                
                                @if($item->getPropertyValue('content', 'summary'))
                                    <p>{{ Str::limit(strip_tags($item->getPropertyValue('content', 'summary')), 150) }}</p>
                                @endif
                                
                                <div class="meta">
                                    <span class="document-type">{{ $item->document_type }}</span>
                                    <span class="publication-date">{{ $item->published_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="pagination">
                        {{ $results->appends(request()->except('page'))->links() }}
                    </div>
                @else
                    <div class="no-results">
                        <p>No results found. Please try different keywords.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-inspirecms-your-theme::layout>
```

### Registering the Search Route

Register the search route in your `routes/web.php` file:

```php
Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');
```

## Advanced Search Configuration

### Search Configuration Options

You can configure search behavior in your `config/inspirecms.php` file:

```php {title="config/inspirecms.php"}
'search' => [
    'driver' => env('SEARCH_DRIVER', 'database'), // Options: database, elastic, algolia, custom
    'index_prefix' => env('SEARCH_INDEX_PREFIX', 'inspirecms_'),
    'min_search_length' => 2,
    'highlight' => [
        'enabled' => true,
        'tag' => 'mark',
    ],
    'weights' => [
        'title' => 10,
        'content' => 5,
        'tags' => 3,
        'path' => 2,
    ],
    'include_fields' => [
        'title',
        'properties.content.body',
        'properties.seo.meta_description',
        'properties.categorization.tags',
    ],
],
```

### Custom Search Provider

You can create a custom search provider to implement specialized search functionality:

```php
namespace App\Search;

use SolutionForest\InspireCms\Search\SearchProviderInterface;
use SolutionForest\InspireCms\Models\Content;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomSearchProvider implements SearchProviderInterface
{
    public function search(string $query, array $options = []): LengthAwarePaginator
    {
        // Custom search logic
        // ...
        
        // Return paginated results
        return new LengthAwarePaginator(
            $items,
            $totalCount,
            $options['per_page'] ?? 10,
            $options['page'] ?? 1,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
    
    public function indexContent(Content $content): void
    {
        // Logic to index content
        // ...
    }
    
    public function removeFromIndex(Content $content): void
    {
        // Logic to remove content from index
        // ...
    }
    
    public function updateIndex(Content $content): void
    {
        // Logic to update content in index
        // ...
    }
}
```

Register your custom search provider:

```php {title="config/inspirecms.php"}
'search' => [
    'driver' => 'custom',
    'providers' => [
        'custom' => \App\Search\CustomSearchProvider::class,
    ],
    // Other search settings...
],
```

## Integrating Elasticsearch

InspireCMS can integrate with Elasticsearch for more powerful search capabilities:

### Installation and Configuration

1. Install the required packages:

```bash
composer require elasticsearch/elasticsearch
```

2. Configure Elasticsearch connection:

```php {title="config/inspirecms.php"}
'search' => [
    'driver' => 'elastic',
    'connections' => [
        'elastic' => [
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'localhost:9200'),
            ],
            'username' => env('ELASTICSEARCH_USERNAME'),
            'password' => env('ELASTICSEARCH_PASSWORD'),
        ],
    ],
    'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'inspirecms_'),
    'index_settings' => [
        'number_of_shards' => 1,
        'number_of_replicas' => 0,
    ],
],
```

3. Create an Elasticsearch mapping:

```php
namespace App\Console\Commands;

use Elasticsearch\Client;
use Illuminate\Console\Command;

class CreateElasticsearchMapping extends Command
{
    protected $signature = 'elasticsearch:create-mapping';
    protected $description = 'Create Elasticsearch mapping for InspireCMS content';
    
    public function handle(Client $elasticsearch)
    {
        $index = config('inspirecms.search.index_prefix') . 'content';
        
        // Delete index if it exists
        if ($elasticsearch->indices()->exists(['index' => $index])) {
            $elasticsearch->indices()->delete(['index' => $index]);
        }
        
        // Create index with mapping
        $elasticsearch->indices()->create([
            'index' => $index,
            'body' => [
                'settings' => config('inspirecms.search.index_settings'),
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'keyword'],
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'standard',
                            'fields' => [
                                'keyword' => ['type' => 'keyword'],
                            ],
                        ],
                        'path' => ['type' => 'keyword'],
                        'document_type' => ['type' => 'keyword'],
                        'content' => ['type' => 'text'],
                        'tags' => ['type' => 'keyword'],
                        'published_at' => ['type' => 'date'],
                        // Additional fields...
                    ],
                ],
            ],
        ]);
        
        $this->info("Index {$index} created successfully.");
    }
}
```

4. Index content to Elasticsearch:

```php
namespace App\Console\Commands;

use Elasticsearch\Client;
use Illuminate\Console\Command;
use SolutionForest\InspireCms\Models\Content;

class IndexContentToElasticsearch extends Command
{
    protected $signature = 'elasticsearch:index-content {--fresh} {--id=}';
    protected $description = 'Index InspireCMS content to Elasticsearch';
    
    public function handle(Client $elasticsearch)
    {
        $index = config('inspirecms.search.index_prefix') . 'content';
        
        if ($this->option('fresh')) {
            $this->call('elasticsearch:create-mapping');
        }
        
        $query = Content::query()->isPublished();
        
        if ($id = $this->option('id')) {
            $query->where('id', $id);
            $this->info("Indexing content with ID: {$id}");
        } else {
            $this->info("Indexing all published content");
        }
        
        $count = 0;
        $query->chunk(100, function ($contents) use ($elasticsearch, $index, &$count) {
            foreach ($contents as $content) {
                $this->indexContent($elasticsearch, $index, $content);
                $count++;
            }
        });
        
        $this->info("Indexed {$count} content items.");
    }
    
    protected function indexContent(Client $elasticsearch, string $index, Content $content)
    {
        // Extract searchable text from content properties
        $searchableText = $this->extractSearchableText($content);
        
        $elasticsearch->index([
            'index' => $index,
            'id' => $content->id,
            'body' => [
                'id' => $content->id,
                'title' => $content->getTitle(),
                'path' => $content->getPath(),
                'document_type' => $content->document_type,
                'content' => $searchableText,
                'tags' => $this->extractTags($content),
                'published_at' => $content->published_at?->format('c'),
            ],
        ]);
    }
    
    protected function extractSearchableText(Content $content)
    {
        $text = $content->getTitle() . ' ';
        
        // Add body content
        if ($content->hasProperty('content', 'body')) {
            $text .= strip_tags($content->getPropertyValue('content', 'body')) . ' ';
        }
        
        // Add summary
        if ($content->hasProperty('content', 'summary')) {
            $text .= strip_tags($content->getPropertyValue('content', 'summary')) . ' ';
        }
        
        // Add meta description
        if ($content->hasProperty('seo', 'meta_description')) {
            $text .= $content->getPropertyValue('seo', 'meta_description') . ' ';
        }
        
        return $text;
    }
    
    protected function extractTags(Content $content)
    {
        $tags = [];
        
        // Extract tags from content
        if ($content->hasProperty('categorization', 'tags')) {
            $tags = array_merge($tags, (array) $content->getPropertyValue('categorization', 'tags'));
        }
        
        return $tags;
    }
}
```