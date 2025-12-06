# ChromaDB PHP

**A customized, framework-agnostic PHP library for interacting with [Chroma](https://github.com/chroma-core/chroma) vector database seamlessly.**

[![Total Downloads](https://img.shields.io/packagist/dt/codewithkyrian/chromadb-php.svg)](https://packagist.org/packages/codewithkyrian/chromadb-php)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/codewithkyrian/chromadb-php.svg)](https://packagist.org/packages/codewithkyrian/chromadb-php)
[![MIT Licensed](https://img.shields.io/badge/license-mit-blue.svg)](https://github.com/CodeWithKyrian/chromadb-php/blob/main/LICENSE)
[![GitHub Tests Action Status](https://github.com/CodeWithKyrian/chromadb-php/actions/workflows/test.yml/badge.svg)](https://github.com/CodeWithKyrian/chromadb-php/actions/workflows/test.yml)

> **Note:** This package is framework-agnostic. If you use **Laravel**, check out [chromadb-laravel](https://github.com/CodeWithKyrian/chromadb-laravel) for a tailored experience.

## Introduction

[Chroma](https://www.trychroma.com/) is an open-source vector database designed to be fast, scalable, and reliable. ChromaDB PHP allows you to interact with Chroma servers seamlessly. It provides a fluent, type-safe API for managing collections, documents, and embeddings, making it easy to build LLM-powered applications in PHP.

## Requirements

- PHP 8.1 or higher
- ChromaDB 1.0 or higher

## Installation

```bash
composer require codewithkyrian/chromadb-php
```

## Configuration & Setup

### Running ChromaDB

You need a running ChromaDB instance.

**Docker (Recommended):**
```bash
docker run -p 8000:8000 chromadb/chroma
```

**Chroma CLI:**
```bash
chroma run --path /path/to/data
```

### Connectivity

Connect to your Chroma server. The default connection is `http://localhost:8000`.

```php
use Codewithkyrian\ChromaDB\ChromaDB;

// Basic Connection
$client = ChromaDB::local()->connect();

// Custom Host/Port
$client = ChromaDB::local()
    ->withHost('http://your-server-ip')
    ->withPort(8000)
    ->withTenant('my-tenant')
    ->withDatabase('production_db')
    ->connect();

// Chroma Cloud / Authentication
$client = ChromaDB::cloud('your-api-key')
    ->withTenant('tenant-id')
    ->connect();
```

## Embedding Functions

ChromaDB uses embedding functions to convert text into vectors. You can define which function a collection uses upon creation.

Embedding functions are linked to a collection and used when you call `add`, `update`, `upsert` or `query`. If you add documents *without* embeddings, it is used to generate them automatically. If you query using text, it is used to convert your query text into a vector for search.

The library provides lightweight wrappers around popular embedding providers for ease of use:

- `OpenAIEmbeddingFunction`
- `JinaEmbeddingFunction`
- `HuggingFaceEmbeddingServerFunction`
- `OllamaEmbeddingFunction`
- `MistralAIEmbeddingFunction`

Example:

```php
use Codewithkyrian\ChromaDB\Embeddings\OpenAIEmbeddingFunction;

$ef = new OpenAIEmbeddingFunction('your-openai-api-key');

$collection = $client->createCollection(
    name: 'knowledge-base',
    embeddingFunction: $ef
);
```

### Custom Functions
You can create your own embedding function by implementing `Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction`.

```php
use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;

$ef = new class implements EmbeddingFunction {
    public function generate(array $texts): array {
        // Call your model API here and return float[][]
        return [[0.1, 0.2, ...], ...];
    }
};
```

## Collections

Collections are where you store and categorize your embeddings and documents. All operations are performed on a specific collection.

```php
// Create (throws if exists)
$collection = $client->createCollection('my-collection', $ef);

// Get (throws if missing)
$collection = $client->getCollection('my-collection');

// Get or Create =
$collection = $client->getOrCreateCollection('my-collection', $ef);

// Delete
$client->deleteCollection('my-collection');
```

## Adding Data

You can add items to a collection using the structured `Record` class or raw arrays. Both methods represent the same data:

- **IDs** (Required): Unique string identifier.
- **Embeddings**: Vector representation (float array).
- **Documents**: Raw text content.
- **Metadatas**: Key-value pairs for filtering.

### Using Arrays
You can pass a parallel arrays of IDs, embeddings, metadatas, etc. This is useful for bulk operations.

```php
$collection->add(
    ids: ['id1', 'id2'],
    documents: ['This is a document about PHP.', 'ChromaDB is great for AI.'],
    embeddings: [[0.1, 0.2, 0.3], [0.9, 0.8, 0.7]],
    metadatas: [
        ['category' => 'development', 'author' => 'Kyrian'],
        ['category' => 'ai', 'is_published' => true]
    ]
);
```

### Using Records (Fluent API)
The `Record` class provides a fluent interface for building items. It mirrors the array structure but in an object-oriented way.

```php
use Codewithkyrian\ChromaDB\Types\Record;

$collection->add([
    // Fluent Factory style
    Record::make('id4')
        ->withDocument('This is a document about PHP.')
        ->withEmbedding([0.1, 0.2, 0.3])
        ->withMetadata(['category' => 'development', 'author' => 'Kyrian']),

    // Constructor style
    new Record(
        id: 'id7',
        document: 'ChromaDB is great for AI.',
        embedding: [0.9, 0.8, 0.7],
        metadata: ['category' => 'ai', 'is_published' => true]
    ),
]);
```

If you provide `documents` but *omit* `embeddings`, Chroma uses the collection's **Embedding Function** to generate them. This is useful if you have an external embedding function or if you want to manually control the embedding process. When providing just embeddings and not documents, it's assumed you're storing the documents elsewhere and associating the provided embeddings with those documents using the `ids` or any other metadata.

> If the supplied embeddings are not the same dimension as the embeddings already indexed in the collection, an exception will be raised.

## Retrieval (`get` and `peek`)

Retrieve specific items by ID or filtered metadata without generating embeddings.

### Get
Fetch specific items.

```php
use Codewithkyrian\ChromaDB\Types\Includes;

// Fetch by ID
$item = $collection->get(ids: ['id1']);

// Fetch filtered items (Metadata Filter)
$items = $collection->get(
    where: ['category' => 'php'], 
    include: [Includes::Documents, Includes::Metadatas]
);

// Fetch items as Record objects
$records = $items->asRecords();
```

### Peek
Preview the first `n` items in the collection.

```php
$preview = $collection->peek(limit: 5);
```

### Specifying Return Data (`include`)
Both `get` and `query` allow you to specify what data to return using the `include` parameter.

```php
use Codewithkyrian\ChromaDB\Types\Includes;

$collection->get(
    ids: ['id1'],
    include: [
        Includes::Documents, // Return the document text
        Includes::Metadatas, // Return the metadata
        Includes::Embeddings // Return the vector
    ]
);
```
> **Note:** `Includes::Distances` is only available when **Querying**, not when using `get()`.

## Querying (Vector Search)

Querying is about finding items *semantically similar* to your input. Chroma performs a vector search to find the nearest neighbors. ChromaDB-PHP also provides a powerful, fluent query builder for filtering by metadata and document content.

### Query by Text

Provide text strings. Chroma embeds them using the collection's Embedding Function and finds the nearest neighbors.


```php
$results = $collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5 // Return top 5 matches
);

// Get results as ScoredRecord objects
// Returns ScoredRecord[][] (one array of results per query text)
$records = $results->asRecords();
```

### Query by Embeddings
Provide raw vectors. Useful if you compute embeddings externally.

```php
$results = $collection->query(
    queryEmbeddings: [[0.1, 0.2, ...]], 
    nResults: 5
);
```

### Specifying Return Data (`include`)

By default, queries return IDs, Embeddings, Metadatas, and Distances. You can customize this using the `Includes` enum to optimize performance.

```php
use Codewithkyrian\ChromaDB\Types\Includes;

$collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5,
    include: [
        Includes::Documents, // Return the actual text content
        Includes::Distances // Return the similarity score
    ]
);
```

### Metadata Filtering (`where`)
You can filter search results based on metadata of the items. The library provides a fluent **Builder** for safety, but also supports raw arrays.

### Supported Comparisons

```php
// Equals
Where::field('category')->eq('news');
['category' => ['$eq' => 'news']];

// Not Equals
Where::field('status')->ne('archived');
['status' => ['$ne' => 'archived']];

// Greater Than
Where::field('views')->gt(100);
['views' => ['$gt' => 100]];

// Less Than
Where::field('rating')->lt(5);
['rating' => ['$lt' => 5]];

// Greater Than or Equal To
Where::field('views')->gte(100);
['views' => ['$gte' => 100]];

// Less Than or Equal To
Where::field('rating')->lte(5);
['rating' => ['$lte' => 5]];

// List inclusion
Where::field('tag')->in(['php', 'laravel']);
['tag' => ['$in' => ['php', 'laravel']]];

// List exclusion
Where::field('tag')->nin(['php', 'laravel']);
['tag' => ['$nin' => ['php', 'laravel']]];

// Logical AND
Where::all(
    Where::field('category')->eq('code'),
    Where::field('language')->eq('php')
) ;
['$and' => [
    ['category' => ['$eq' => 'code']],
    ['language' => ['$eq' => 'php']]
]]

// Logical OR
Where::any(
    Where::field('category')->eq('code'),
    Where::field('language')->eq('php')
) ;
['$or' => [
    ['category' => ['$eq' => 'code']],
    ['language' => ['$eq' => 'php']]
]]
```

#### Usage

```php
$collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5,
    where: Where::field('category')->eq('code')
);

$collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5,
    where: ['category' => ['$eq' => 'code']]
);

$collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5,
    where: Where::all(
        Where::field('category')->eq('code'),
        Where::field('language')->eq('php')
    )
);

$collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5,
    where: ['$and' => [
        ['category' => ['$eq' => 'code']],
        ['language' => ['$eq' => 'php']]
    ]]
);
```

### Full Text Search (`whereDocument`)

Used to filter based on the text content of the document itself. This supports **substring matching** and **Regex**. You can also use the fluent builder or array syntax.

#### Supported Comparisons

```php
// Substring (Contains)
Where::document()->contains('search term')
['$contains' => 'search term']

// Substring (Not Contains)
Where::document()->notContains('spam')
['$not_contains' => 'spam']

// Regex Matching
Where::document()->matches('^PHP 8\.[0-9]+')
['$regex' => '^PHP 8\.[0-9]+']

Where::document()->notMatches('deprecated')
['$not_regex' => 'deprecated']

// Logical OR
Where::any(
    Where::document()->contains('php'),
    Where::document()->contains('laravel')
)
['$or' => [
    ['document' => ['$contains' => 'php']],
    ['document' => ['$contains' => 'laravel']]
]]

// Logical AND
Where::all(
    Where::document()->contains('php'),
    Where::document()->contains('laravel')
)
['$and' => [
    ['document' => ['$contains' => 'php']],
    ['document' => ['$contains' => 'laravel']]
]]
```

#### Usage

```php
$collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5,
    whereDocument: Where::document()->contains('php')
);

$collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5,
    whereDocument: ['$contains' => 'php']
);

$collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5,
    whereDocument: Where::any(
        Where::document()->contains('php'),
        Where::document()->contains('laravel')
    )
);

$collection->query(
    queryTexts: ['How do I use PHP with Chroma?'], 
    nResults: 5,
    whereDocument: ['$or' => [
        ['$contains' => 'php'],
        ['$contains' => 'laravel']
    ]]
);
```

## Updating Data

Use `update` to modify existing items (fails if ID missing) or `upsert` to update-or-create. Just like adding, you can either pass an array of records, or a parallel array of IDs, documents, and metadatas.

```php
// Update using Records
$collection->update([
    Record::make('id1')->withMetadata(['updated' => true])
]);

// Upsert using Arrays
$collection->upsert(
    ids: ['id_new'],
    documents: ['New document content'],
    metadatas: [['created' => 'now']]
);
```

## Deleting Data

Delete by IDs or by filter.

```php
// Delete specific items
$collection->delete(['id1', 'id2']);

// Delete all items matching a filter
$collection->delete(where: Where::field('category')->eq('outdated'));

// Delete all items matching a document content filter
$collection->delete(whereDocument: Where::document()->contains('outdated'));
```

## Testing

Run the test suite using Pest.

```bash
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for more information.
