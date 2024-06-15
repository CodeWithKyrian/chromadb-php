## ChromaDB PHP

**A PHP library for interacting with [Chroma](https://github.com/chroma-core/chroma) vector database seamlessly.**

[![Total Downloads](https://img.shields.io/packagist/dt/codewithkyrian/chromadb-php.svg)](https://packagist.org/packages/codewithkyrian/chromadb-php)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/codewithkyrian/chromadb-php.svg)](https://packagist.org/packages/codewithkyrian/chromadb-php)
[![MIT Licensed](https://img.shields.io/badge/license-mit-blue.svg)](https://github.com/CodeWithKyrian/chromadb-php/blob/main/LICENSE)
[![GitHub Tests Action Status](https://github.com/CodeWithKyrian/chromadb-php/actions/workflows/test.yml/badge.svg)](https://github.com/CodeWithKyrian/chromadb-php/actions/workflows/test.yml)

> **Note:** This package is framework-agnostic, and can be used in any PHP project. If you're using Laravel however, you
> might want to check out the Laravel-specific package [here](https://github.com/CodeWithKyrian/chromadb-laravel) which
> provides a more Laravel-like experience, and includes a few extra features.

## Description

[Chroma](https://www.trychroma.com/) is an open-source vector database that allows you to store, search, and analyze high-dimensional data at scale.
It is designed to be fast, scalable, and reliable. It makes it easy to build LLM (Large Language Model) applications and
services that require high-dimensional vector search.

ChromaDB PHP provides a simple and intuitive interface for interacting with Chroma from PHP. It enables you to:

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
  chroma:
    image: 'chromadb/chroma'
    ports:
      - '8000:8000'
    volumes:
      - chroma-data:/chroma/chroma

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

$chroma = ChromaDB::client();

```

By default, ChromaDB will try to connect to `http://localhost:8000` using the default database name `default_database`
and default tenant name `default_tenant`. You can however change these values by constructing the client using the
factory method:

```php
use Codewithkyrian\ChromaDB\ChromaDB;

$chroma = ChromaDB::factory()
                ->withHost('http://localhost')
                ->withPort(8000)
                ->withDatabase('new_database')
                ->withTenant('new_tenant')
                ->connect();                
```

If the tenant or database doesn't exist, the package will automatically create them for you.

### Authentication

ChromaDB supports static token-based authentication. To use it, you need to start the Chroma server passing the required
environment variables as stated in the documentation. If you're using the docker image, you can pass in the environment
variables using the `--env` flag or by using a `.env` file and for the docker-compose file, you can use the `env_file`
option, or pass in the environment variables directly like so:

```yaml
version: '3.9'
  
services:
  chroma:
    image: 'chromadb/chroma'
    ports:
      - '8000:8000'
    environment:
      - CHROMA_SERVER_AUTHN_CREDENTIALS=test-token
      - CHROMA_SERVER_AUTHN_PROVIDER=chromadb.auth.token_authn.TokenAuthenticationServerProvider
      
    ...
```   
    
You can then connect to ChromaDB using the factory method:

```php
use Codewithkyrian\ChromaDB\ChromaDB;

$chroma = ChromaDB::factory()
                ->withAuthToken('test-token')
                ->connect();                
```

### Getting the version

```php

echo $chroma->version(); // 0.4.0

```

### Creating a Collection

Creating a collection is as simple as calling the `createCollection` method on the client and passing in the name of
the collection.

```php

$collection = $chroma->createCollection('test-collection');

```

If the collection already exists in the database, the package will throw an exception.

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
- `embeddings`: An array of document embeddings. The embeddings must be a 1D array of floats with a consistent length. You
  can compute the embeddings using any embedding model of your choice (just make sure that's what you use when querying as
  well).
- `metadatas`: An array of document metadatas. The metadatas must be an array of key-value pairs.

If you don't have the embeddings, you can pass in the documents and provide an embedding function that will be used to
compute the embeddings for you.

### Passing in Embedding Function

To use an embedding function, you need to pass it in as an argument when creating the collection:

```php
use CodeWithKyrian\ChromaDB\EmbeddingFunction\EmbeddingFunctionInterface;

$embeddingFunction = new OpenAIEmbeddingFunction('api-key', 'org-id', 'model-name');

$collection = $chroma->createCollection('test-collection', embeddingFunction: $embeddingFunction);
```

The embedding function must be an instance of `EmbeddingFunctionInterface`. There are a few built-in embedding functions
that you can use:

- `OpenAIEmbeddingFunction`: This embedding function uses the OpenAI API to compute the embeddings. You can use it like
  this:
    ```php
    use CodeWithKyrian\Chroma\EmbeddingFunction\OpenAIEmbeddingFunction;
    
    $embeddingFunction = new OpenAIEmbeddingFunction('api-key', 'org-id', 'model-name');
    
    $collection = $chromaDB->createCollection('test-collection', embeddingFunction: $embeddingFunction);
    ```
  You can get your OpenAI API key and organization id from your [OpenAI dashboard](https://beta.openai.com/), and you
  can omit the organization id if your API key doesn't belong to an organization. The model name is optional as well and
  defaults to `text-embedding-ada-002`

- `JinaEmbeddingFunction`: This is a wrapper for the Jina Embedding models. You can use by passing your Jina API key and
  the desired model. THis defaults to `jina-embeddings-v2-base-en`
    ```php
  use Codewithkyrian\ChromaDB\Embeddings\JinaEmbeddingFunction;
  
  $embeddingFunction = new JinaEmbeddingFunction('api-key');
  
  $collection = $chromaDB->createCollection('test-collection', embeddingFunction: $embeddingFunction);
    ```

- `HuggingFaceEmbeddingServerFunction`: This embedding function is a wrapper around the HuggingFace Text Embedding
  Server. Before using it, you need to have
  the [HuggingFace Embedding Server](https://github.com/huggingface/text-embeddings-inference) running somewhere locally.  Here's how you can use it:
    ```php
    use CodeWithKyrian\Chroma\EmbeddingFunction\HuggingFaceEmbeddingFunction;
    
    $embeddingFunction = new HuggingFaceEmbeddingFunction('api-key', 'model-name');
    
    $collection = $chromaDB->createCollection('test-collection', embeddingFunction: $embeddingFunction);
    ```

Besides the built-in embedding functions, you can also create your own embedding function by implementing
the `EmbeddingFunction` interface (including Anonymous Classes):

```php
use CodeWithKyrian\ChromaDB\EmbeddingFunction\EmbeddingFunctionInterface;

$embeddingFunction = new class implements EmbeddingFunctionInterface {
    public function generate(array $texts): array
    {
        // Compute the embeddings here and return them as an array of arrays
    }
};

$collection = $chroma->createCollection('test-collection', embeddingFunction: $embeddingFunction);
```

> The embedding function will be called for each batch of documents that are inserted into the collection, and must be
> provided either when creating the collection or when querying the collection. If you don't provide an embedding
> function, and you don't provide the embeddings, the package will throw an exception.

### Inserting Documents into a Collection with an Embedding Function

```php
$ids = ['test1', 'test2', 'test3'];
$documents = [
    'This is a test document',
    'This is another test document',
    'This is yet another test document',
];
$metadatas = [
    ['url' => 'https://example.com/test1'],
    ['url' => 'https://example.com/test2'],
    ['url' => 'https://example.com/test3'],
];

$collection->add(
    ids: $ids, 
    documents: $documents, 
    metadatas: $metadatas
);
```

### Getting a Collection

```php
$collection = $chromaDB->getCollection('test-collection');
```

Or with an embedding function:

```php
$collection = $chromaDB->getCollection('test-collection', embeddingFunction: $embeddingFunction);
```

> Make sure that the embedding function you provide is the same one that was used when creating the collection.

### Counting the items in a collection

```php
$collection->count() // 2
```

### Updating a collection

```php
$collection->update(
    ids: ['test1', 'test2', 'test3'],
    embeddings: [
        [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
        [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
        [10.0, 9.0, 8.0, 7.0, 6.0, 5.0, 4.0, 3.0, 2.0, 1.0],
    ],
    metadatas: [
        ['url' => 'https://example.com/test1'],
        ['url' => 'https://example.com/test2'],
        ['url' => 'https://example.com/test3'],
    ]
);
```

### Deleting Documents

```php
$collection->delete(['test1', 'test2', 'test3']);
```

### Querying a Collection

```php
$queryResponse = $collection->query(
    queryEmbeddings: [
        [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
    ],
    nResults: 2
);

echo $queryResponse->ids[0][0]; // test1
echo $queryResponse->ids[0][1]; // test2
```

To query a collection, you need to provide the following:

- `queryEmbeddings` (optional): An array of query embeddings. The embeddings must be a 1D array of floats. You
  can compute the embeddings using any embedding model of your choice (just make sure that's what you use when inserting
  as
  well).
- `nResults`: The number of results to return. Defaults to 10.
- `queryTexts` (optional): An array of query texts. The texts must be strings. You can omit this if you provide the
  embeddings. Here's
  an example:
    ```php
    $queryResponse = $collection->query(
        queryTexts: [
            'This is a test document'
        ],
        nResults: 2
    );
    
    echo $queryResponse->ids[0][0]; // test1
    echo $queryResponse->ids[0][1]; // test2
    ```
- `where` (optional): The where clause to use to filter items based on their metadata. Here's an example:
  ```php
  $queryResponse = $collection->query(
      queryEmbeddings: [
          [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
      ],
      nResults: 2,
      where: [
          'url' => 'https://example.com/test1'
      ]
  );
      
  echo $queryResponse->ids[0][0]; // test1
  ```
  The where clause must be an array of key-value pairs. The key must be a string, and the value can be a string or
  an array of valid filter values. Here are the valid filters (`$eq`, `$ne`, `$in`, `$nin`, `$gt`, `$gte`, `$lt`,
  `$lte`):
    - `$eq`: Equals
    - `$ne`: Not equals
    - `$gt`: Greater than
    - `$gte`: Greater than or equal to
    - `$lt`: Less than
    - `$lte`: Less than or equal to

  Here's an example:
  ```php
    $queryResponse = $collection->query(
        queryEmbeddings: [
            [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
        ],
        nResults: 2,
        where: [
            'url' => [
                '$eq' => 'https://example.com/test1'
            ]
        ]
    );
  ```
  You can also use multiple filters:
    ```php
        $queryResponse = $collection->query(
            queryEmbeddings: [
                [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
            ],
            nResults: 2,
            where: [
                'url' => [
                    '$eq' => 'https://example.com/test1'
                ],
                'title' => [
                    '$ne' => 'Test 1'
                ]
            ]
        );
    ```
- `whereDocument` (optional): The where clause to use to filter items based on their document. Here's an example:
  ```php
  $queryResponse = $collection->query(
      queryEmbeddings: [
          [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
      ],
      nResults: 2,
      whereDocument: [
          'text' => 'This is a test document'
      ]
  );
          
  echo $queryResponse->ids[0][0]; // test1
  ```
  The where clause must be an array of key-value pairs. The key must be a string, and the value can be a string or
  an array of valid filter values. In this case, only two filtering keys are supported - `$contains`
  and `$not_contains`.

  Here's an example:
  ```php
    $queryResponse = $collection->query(
        queryEmbeddings: [
            [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
        ],
        nResults: 2,
        whereDocument: [
            'text' => [
                '$contains' => 'test document'
            ]
        ]
    );
  ```
- `include` (optional): An array of fields to include in the response. Possible values
  are `embeddings`, `documents`, `metadatas` and `distances`. It defaults to `embeddings`
  and `metadatas` (`documents` are not included by default because they can be large).
  ```php
  $queryResponse = $collection->query(
      queryEmbeddings: [
          [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
      ],
      nResults: 2,
      include: ['embeddings']
  );
  ```
  `distances` is only valid for querying and not for getting. It returns the distances between the query embeddings
  and the embeddings of the results.

Other relevant information about querying and retrieving a collection can be found in the [ChromaDB Documentation](https://docs.trychroma.com/usage-guide).

### Deleting items in a collection

To delete the documents in a collection, pass in an array of the ids of the items:

```php
$collection->delete(['test1', 'test2']);

$collection->count() // 1
```

Passing the ids is optional. You can delete items from a collection using a where filter:

```php
$collection->add(
    ['test1', 'test2', 'test3'],
    [
        [1.0, 2.0, 3.0, 4.0, 5.0],
        [6.0, 7.0, 8.0, 9.0, 10.0],
        [11.0, 12.0, 13.0, 14.0, 15.0],
    ], 
     [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ]
);

$collection->delete(
    where: [
        'some' => 'metadata1'
    ]
);

$collection->count() // 2
```

### Deleting a collection

Deleting a collection is as simple as passing in the name of the collection to be deleted.

```php
$chroma->deleteCollection('test_collection');
```

## Testing

```
// Run chroma by running the docker compose file in the repo
docker compose up -d

composer test
```

## Contributors

- [Kyrian Obikwelu](https://github.com/CodeWithKyrian)
- Other contributors are welcome.

## License

This project is licensed under the MIT License. See
the [LICENSE](https://github.com/codewithkyrian/chromadb-php/blob/main/LICENSE) file for more information.










