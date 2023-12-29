## ChromaDB PHP

**A PHP library for interacting with ChromaDB vector database seamlessly.**

[![Build Status](https://travis-ci.org/ChromaDatabase/ChromaDB-PHP.svg?branch=master)](https://travis-ci.org/ChromaDatabase/ChromaDB-PHP)
[![Coverage Status](https://coveralls.io/repos/github/ChromaDatabase/ChromaDB-PHP/badge.svg?branch=master)](https://coveralls.io/github/ChromaDatabase/ChromaDB-PHP?branch=master)
[![Latest Stable Version](https://poser.pugx.org/chromadb/chromadb-php/v/stable)](https://packagist.org/packages/chromadb/chromadb-php)
[![Total Downloads](https://poser.pugx.org/chromadb/chromadb-php/downloads)](https://packagist.org/packages/chromadb/chromadb-php)
[![License](https://poser.pugx.org/chromadb/chromadb-php/license)](https://packagist.org/packages/chromadb/chromadb-php)

## Description

Chroma is an open-source vector database that allows you to store, search, and analyze high-dimensional data at scale.
It is designed to be fast, scalable, and reliable. It makes it easy to build LLM (Large Language Model) applications and
services that require high-dimensional vector search.

Chroma PHP provides a simple and intuitive interface for interacting with ChromaDB from PHP. It enables you to:

- Create, read, update, and delete documents.
- Execute queries and aggregations.
- Manage collections and indexes.
- Handle authentication and authorization.
- Utilize other ChromaDB features seamlessly.
- And more...

## Small Example

```php
use Codewithkyrian\ChromaDB\ChromaDB;

$chromaDB = ChromaDB::client();

// Check current ChromaDB version
echo $chromaDB->version();

// Create a collection
$collection = $chromaDB->createCollection('test-collection');

echo $collection->name; // test-collection
echo $collection->id; // xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxx

// Insert some documents into the collection
$ids = ['test1', 'test2', 'test3'];
$embeddings = [
    [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
    [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
    [10.0, 9.0, 8.0, 7.0, 6.0, 5.0, 4.0, 3.0, 2.0, 1.0],
];
$metadatas = [
    ['url' => 'https://example.com/test1'],
    ['url' => 'https://example.com/test2'],
    ['url' => 'https://example.com/test3'],
];

$collection->add($ids, $embeddings, $metadatas);

// Search for similar embeddings
$queryResponse = $collection->query(
    queryEmbeddings: [
        [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
    ],
    nResults: 2
);

// Print results
echo $queryResponse->ids[0][0]; // test1
echo $queryResponse->ids[0][1]; // test2


```

## Requirements

- PHP 8.1 or higher
- ChromaDB 0.4.0 or higher running in client/server mode

## Running ChromaDB

In order to use this library, you need to have ChromaDB running somewhere. You can either run it locally or in the
cloud.
(Chroma doesn't support cloud yet, but it will soon.)

For now, ChromaDB can only run in-memory in Python. You can however run it in client/server mode by either running the
python
project or using the docker image (recommended).

To run the docker image, you can use the following command:

```bash
docker run -p 8000:8000 chromadb/chroma
```

You can also pass in some environment variables using a `.env` file:

```bash
docker run -p 8000:8000 --env-file .env chromadb/chroma
```

Or if you prefer using a docker-compose file, you can use the following:

```yaml
version: '3.9'

services:
  server:
    image: 'chromadb/chroma'
    command: uvicorn chromadb.app:app --reload --workers 1 --host 0.0.0.0 --port 8000 --log-config chromadb/log_config.yml --timeout-keep-alive 30
    ports:
      - '8000:8000'
    volumes:
      - chroma-data:/chroma/chroma
    environment:
      - IS_PERSISTENT=TRUE
      - CHROMA_SERVER_NOFILE=65535
      - ALLOW_RESET=true
    networks:
      - net

networks:
  net:
    driver: bridge

volumes:
  chroma-data:
    driver: local
```

And then run it using:

```bash
docker-compose up -d
```

(Check out the [Chroma Documentation](https://docs.trychroma.com/deployment) for more information on how to run
ChromaDB.)

Either way, you can now access ChromaDB at `http://localhost:8000`.

## Installation

```bash
composer require codewithkyrian/chromadb-php
```

## Usage

### Connecting to ChromaDB

```php
use Codewithkyrian\ChromaDB\ChromaDB;

$chromaDB = ChromaDB::client();

```

By default, ChromaDB will try to connect to `http://localhost:8000` using the default database name `default_database`
and default tenant name `default_tenant`. You can however change these values by constructing the client using the 
factory method:

```php
use Codewithkyrian\ChromaDB\ChromaDB;

$chromaDB = ChromaDB::factory()
                ->withHost('http://localhost')
                ->withPort(8000)
                ->withDatabase('new_database')
                ->withTenant('new_tenant')
                ->connect();                
```

If the tenant or database doesn't exist, the package will automatically create them for you.

### Creating a Collection

```php

$collection = $chromaDB->createCollection('test-collection');

```

If the collection already exists in the database, the package will automatically fetch it for you.

### Inserting Documents

```php
$ids = ['test1', 'test2', 'test3'];
$embeddings = [
    [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
    [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
    [10.0, 9.0, 8.0, 7.0, 6.0, 5.0, 4.0, 3.0, 2.0, 1.0],
];
$metadatas = [
    ['url' => 'https://example.com/test1'],
    ['url' => 'https://example.com/test2'],
    ['url' => 'https://example.com/test3'],
];

$collection->add($ids, $embeddings, $metadatas);
```
To insert documents into a collection, you need to provide the following:

- `ids`: An array of document ids. The ids must be unique and must be strings.
- `embeddings`: An array of document embeddings. The embeddings must be a 1D array of floats with a length of 10. You can 
    compute the embeddings using any embedding model of your choice (just make sure that's what you use when querying as well).
- `metadatas`: An array of document metadatas. The metadatas must be an array of key-value pairs.

If you don't have the embeddings, you can pass in the documents and provide an embedding function that will be used to
compute the embeddings for you.

### Passing in Embedding Function

To use an embedding function, you need to pass it in as an argument when creating the collection:

```php
use CodeWithKyrian\ChromaDB\EmbeddingFunction\EmbeddingFunctionInterface;

$embeddingFunction = new OpenAIEmbeddingFunction('api-key', 'org-id', 'model-name');

$collection = $chromaDB->createCollection('test-collection', embeddingFunction: $embeddingFunction);
```

The embedding function must be an instance of `EmbeddingFunctionInterface`. There are a few built-in embedding functions
that you can use:

- `OpenAIEmbeddingFunction`: This embedding function uses the OpenAI API to compute the embeddings. You can use it like this:
    ```php
    use CodeWithKyrian\ChromaDB\EmbeddingFunction\OpenAIEmbeddingFunction;
    
    $embeddingFunction = new OpenAIEmbeddingFunction('api-key', 'org-id', 'model-name');
    
    $collection = $chromaDB->createCollection('test-collection', embeddingFunction: $embeddingFunction);
    ```

- `HuggingFaceEmbeddingFunction`: This embedding function uses the HuggingFace API to compute the embeddings. You can use it like this:

    ```php
    use CodeWithKyrian\ChromaDB\EmbeddingFunction\HuggingFaceEmbeddingFunction;
    
    $embeddingFunction = new HuggingFaceEmbeddingFunction('api-key', 'model-name');
    
    $collection = $chromaDB->createCollection('test-collection', embeddingFunction: $embeddingFunction);
    ```
  
You can also create your own embedding function by implementing the `EmbeddingFunctionInterface` interface.

```php
use CodeWithKyrian\ChromaDB\EmbeddingFunction\EmbeddingFunctionInterface;

$embeddingFunction = new class implements EmbeddingFunctionInterface {
    public function generate(array $texts): array
    {
        // Compute the embeddings here and return them as an array of arrays
    }
};

$collection = $chromaDB->createCollection('test-collection', embeddingFunction: $embeddingFunction);
```











