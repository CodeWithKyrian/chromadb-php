<?php

declare(strict_types=1);

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Codewithkyrian\ChromaDB\Generated\Exceptions\ChromaDimensionalityException;
use Codewithkyrian\ChromaDB\Generated\Exceptions\ChromaException;
use Codewithkyrian\ChromaDB\Generated\Exceptions\ChromaTypeException;
use Codewithkyrian\ChromaDB\Generated\Exceptions\ChromaValueException;
use Codewithkyrian\ChromaDB\Generated\Exceptions\ChromaInvalidArgumentException;
use Codewithkyrian\ChromaDB\Generated\Exceptions\ChromaNotFoundException;
use Codewithkyrian\ChromaDB\Resources\CollectionResource;

beforeEach(function () {
    $this->client = ChromaDB::factory()
        ->withDatabase('test_database')
        ->withTenant('test_tenant')
        ->connect();

    $this->client->deleteAllCollections();

    $this->embeddingFunction = new class implements EmbeddingFunction {
        public function generate(array $texts): array
        {
            return array_map(function ($text) {
                return [1.0, 2.0, 3.0, 4.0, 5.0];
            }, $texts);
        }
    };

    $this->collection = $this->client->createCollection(
        name: 'test_collection',
        embeddingFunction: $this->embeddingFunction
    );
});


it('can get the version', function () {
    $version = $this->client->version();

    expect($version)
        ->toBeString()
        ->toMatch('/^[0-9]+\.[0-9]+\.[0-9]+$/');
});

it('can get the heartbeat', function () {
    $heartbeat = $this->client->heartbeat();

    expect($heartbeat)
        ->toBeInt()
        ->toBeGreaterThan(0);
});

it('can list collections', function () {
    $collections = $this->client->listCollections();

    expect($collections)
        ->toBeArray()
        ->toHaveCount(1);

    $this->client->createCollection('test_collection_2');

    $collections = $this->client->listCollections();

    expect($collections)
        ->toBeArray()
        ->toHaveCount(2);
});


it('can create or get collections', function () {
    $collection = $this->client->getOrCreateCollection('test_collection');

    expect($collection)
        ->toBeInstanceOf(CollectionResource::class)
        ->toHaveProperty('name', 'test_collection');

    $collection = $this->client->getOrCreateCollection('test_collection_2');

    expect($collection)
        ->toBeInstanceOf(CollectionResource::class)
        ->toHaveProperty('name', 'test_collection_2');
});

it('can get a collection', function () {
    $collection = $this->client->getCollection('test_collection');

    expect($collection)
        ->toBeInstanceOf(CollectionResource::class)
        ->toHaveProperty('name', 'test_collection');
});

it('throws a value error when getting a collection that does not exist', function () {
    $this->client->getCollection('test_collection_2');
})->throws(ChromaNotFoundException::class);

it('can modify a collection name or metadata', function () {
    $this->collection->modify('test_collection_2', ['test' => 'test_2']);

    $collection = $this->client->getCollection('test_collection_2');

    expect($collection->name)
        ->toBe('test_collection_2')
        ->and($collection->metadata)
        ->toMatchArray(['test' => 'test_2']);
});

it('can delete a collection', function () {
    $this->client->deleteCollection('test_collection');

    expect(fn() => $this->client->getCollection('test_collection'))
        ->toThrow(ChromaNotFoundException::class);
});

it('can delete all collections', function () {
    $this->client->createCollection('test_collection_2');

    $collections = $this->client->listCollections();

    expect($collections)
        ->toBeArray()
        ->toHaveCount(2);

    $this->client->deleteAllCollections();

    $collections = $this->client->listCollections();

    expect($collections)
        ->toBeArray()
        ->toHaveCount(0);
});

it('throws a value error when deleting a collection that does not exist', function () {
    $this->client->deleteCollection('test_collection_2');
})->throws(ChromaNotFoundException::class);

it('can add single embeddings to a collection', function () {
    $ids = ['test1'];
    $embeddings = [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
    $metadatas = [['test' => 'test']];

    $this->collection->add($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(1);
});

it('cannot add invalid single embeddings to a collection', function () {
    $ids = ['test1'];
    $embeddings = ['this is not an embedding'];
    $metadatas = [['test' => 'test']];

    $this->collection->add($ids, $embeddings, $metadatas);
})->throws(ChromaException::class);

it('can add single text documents to a collection', function () {
    $ids = ['test1'];
    $documents = ['This is a test document'];
    $metadatas = [['test' => 'test']];

    $this->collection->add(
        $ids,
        metadatas: $metadatas,
        documents: $documents
    );

    expect($this->collection->count())->toBe(1);
});

it('cannot add single embeddings to a collection with a different dimensionality', function () {
    $ids = ['test1'];
    $embeddings = [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
    $metadatas = [['test' => 'test']];

    $this->collection->add($ids, $embeddings, $metadatas);

    // Dimensionality is now 10. Other embeddings must have the same dimensionality.

    $ids = ['test2'];
    $embeddings = [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]];
    $metadatas = [['test' => 'test2']];

    $this->collection->add($ids, $embeddings, $metadatas);
})->throws(ChromaInvalidArgumentException::class, 'Collection expecting embedding with dimension of 10, got 11');

it('can upsert single embeddings to a collection', function () {
    $ids = ['test1'];
    $embeddings = [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
    $metadatas = [['test' => 'test']];

    $this->collection->upsert($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(1);

    $this->collection->upsert($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(1);
});


it('can update single embeddings in a collection', function () {
    $ids = ['test1'];
    $embeddings = [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
    $metadatas = [['test' => 'test']];

    $this->collection->add($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(1);

    $this->collection->update($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(1);

    $collectionItems = $this->collection->get($ids);

    expect($collectionItems->ids)
        ->toMatchArray($ids)
        ->and($collectionItems->embeddings)
        ->toMatchArray($embeddings)
        ->and($collectionItems->metadatas)
        ->toMatchArray($metadatas);
});

it('can update single documents in a collection', function () {
    $ids = ['test1'];
    $documents = ['This is a test document'];
    $metadatas = [['test' => 'test']];

    $this->collection->add(
        $ids,
        metadatas: $metadatas,
        documents: $documents
    );

    expect($this->collection->count())->toBe(1);

    $newDocuments = ['This is a new test document'];
    $newMetadatas = [['test' => 'test2']];

    $this->collection->update(
        $ids,
        metadatas: $newMetadatas,
        documents: $newDocuments
    );

    expect($this->collection->count())->toBe(1);

    $collectionItems = $this->collection->get($ids, include: ['documents', 'metadatas']);

    expect($collectionItems->ids)
        ->toMatchArray($ids)
        ->and($collectionItems->documents)
        ->toMatchArray($newDocuments)
        ->and($collectionItems->metadatas)
        ->toMatchArray($newMetadatas);
});

it('can add batch embeddings to a collection', function () {
    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        [11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
        [21, 22, 23, 24, 25, 26, 27, 28, 29, 30],
    ];
    $metadatas = [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ];

    $this->collection->add($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(3);

    $getResponse = $this->collection->get($ids);

    expect($getResponse->ids)
        ->toMatchArray($ids)
        ->and($getResponse->embeddings)
        ->toMatchArray($embeddings)
        ->and($getResponse->metadatas)
        ->toMatchArray($metadatas);
});

it('cannot add batch embeddings with different dimensionality to a collection', function () {
    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        [11, 12, 13, 14, 15, 16, 17, 18, 19],
        [21, 22, 23, 24, 25, 26, 27, 28],
    ];
    $metadatas = [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ];

    $this->collection->add($ids, $embeddings, $metadatas);
})->throws(ChromaInvalidArgumentException::class);

it('can add batch documents to a collection', function () {
    $ids = ['test1', 'test2', 'test3'];
    $documents = [
        'This is a test document',
        'This is another test document',
        'This is a third test document',
    ];
    $metadatas = [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ];

    $this->collection->add(
        $ids,
        metadatas: $metadatas,
        documents: $documents
    );

    expect($this->collection->count())->toBe(3);

    $getResponse = $this->collection->get($ids, include: ['documents', 'metadatas']);

    expect($getResponse->ids)
        ->toMatchArray($ids)
        ->and($getResponse->documents)
        ->toMatchArray($documents)
        ->and($getResponse->metadatas)
        ->toMatchArray($metadatas);
});


it('can peek a collection', function () {
    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1.0, 2.0, 3.0, 4.0, 5.0],
        [6.0, 7.0, 8.0, 9.0, 10.0],
        [11.0, 12.0, 13.0, 14.0, 15.0],
    ];

    $this->collection->add($ids, $embeddings);

    expect($this->collection->count())->toBe(3);

    $peekResponse = $this->collection->peek(2);

    expect($peekResponse->ids)
        ->toMatchArray(['test1', 'test2']);
});

it('can query a collection', function () {
    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
        [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
        [10.0, 9.0, 8.0, 7.0, 6.0, 5.0, 4.0, 3.0, 2.0, 1.0],
    ];

    $this->collection->add($ids, $embeddings);

    expect($this->collection->count())->toBe(3);

    $queryResponse = $this->collection->query(
        queryEmbeddings: [
            [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
        ],
        nResults: 2
    );

    expect($queryResponse->ids[0])
        ->toMatchArray(['test1', 'test2'])
        ->and($queryResponse->distances[0])
        ->toMatchArray([0.0, 0.0]);
});

it('can get a collection by id', function () {
    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1.0, 2.0, 3.0, 4.0, 5.0],
        [6.0, 7.0, 8.0, 9.0, 10.0],
        [11.0, 12.0, 13.0, 14.0, 15.0],
    ];
    $metadatas = [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ];

    $this->collection->add($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(3);

    $collectionItems = $this->collection->get(['test1', 'test2']);

    expect($collectionItems->ids)
        ->toMatchArray(['test1', 'test2'])
        ->and($collectionItems->embeddings)
        ->toMatchArray([
            [1.0, 2.0, 3.0, 4.0, 5.0],
            [6.0, 7.0, 8.0, 9.0, 10.0],
        ]);
});


it('can get a collection by where', function () {
    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1.0, 2.0, 3.0, 4.0, 5.0],
        [6.0, 7.0, 8.0, 9.0, 10.0],
        [11.0, 12.0, 13.0, 14.0, 15.0],
    ];
    $metadatas = [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ];

    $this->collection->add($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(3);

    $collectionItems = $this->collection->get(
        where: [
            'some' => ['$eq' => 'metadata1']
        ]
    );

    expect($collectionItems->ids)
        ->toHaveCount(1)
        ->and($collectionItems->ids[0])
        ->toBe('test1');
});

it('can query a collection using query texts', function () {
    $ids = ['test1', 'test2', 'test3'];
    $documents = [
        'This is a test document',
        'This is another test document',
        'This is a third test document',
    ];
    $metadatas = [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ];

    $this->collection->add(
        $ids,
        metadatas: $metadatas,
        documents: $documents
    );

    expect($this->collection->count())->toBe(3);

    $queryResponse = $this->collection->query(
        queryTexts: ['This is a test document'],
        nResults: 1
    );

    expect($queryResponse->ids[0][0])
        ->toBeIn(['test1', 'test2', 'test3']);
});

it('throws a value error when getting a collection by where with an invalid operator', function () {
    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1.0, 2.0, 3.0, 4.0, 5.0],
        [6.0, 7.0, 8.0, 9.0, 10.0],
        [11.0, 12.0, 13.0, 14.0, 15.0],
    ];
    $metadatas = [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ];

    $this->collection->add($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(3);

    $collectionItems = $this->collection->get(
        where: [
            'some' => ['$invalid' => 'metadata1']
        ]
    );
})->throws(ChromaException::class);

it('can delete a collection by id', function () {
    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1.0, 2.0, 3.0, 4.0, 5.0],
        [6.0, 7.0, 8.0, 9.0, 10.0],
        [11.0, 12.0, 13.0, 14.0, 15.0],
    ];
    $metadatas = [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ];

    $this->collection->add($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(3);

    $this->collection->delete(['test1', 'test2']);

    expect($this->collection->count())->toBe(1);
});

it('can delete a collection by where', function () {
    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1.0, 2.0, 3.0, 4.0, 5.0],
        [6.0, 7.0, 8.0, 9.0, 10.0],
        [11.0, 12.0, 13.0, 14.0, 15.0],
    ];
    $metadatas = [
        ['some' => 'metadata1'],
        ['some' => 'metadata2'],
        ['some' => 'metadata3'],
    ];

    $this->collection->add($ids, $embeddings, $metadatas);

    expect($this->collection->count())->toBe(3);

    $this->collection->delete(
        where: [
            'some' => 'metadata1'
        ]
    );

    expect($this->collection->count())->toBe(2);
});
