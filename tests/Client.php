<?php

declare(strict_types=1);

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Generated\Exceptions\ChromaValueException;
use Codewithkyrian\ChromaDB\Resources\CollectionResource;

beforeEach(function () {
    $this->client = ChromaDB::factory()
        ->withDatabase('test_database')
        ->withTenant('test_tenant')
        ->connect();

    $this->client->deleteAllCollections();
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
        ->toHaveCount(0);

    $this->client->createCollection('test_collection');
    $this->client->createCollection('test_collection_2');

    $collections = $this->client->listCollections();

    expect($collections)
        ->toBeArray()
        ->toHaveCount(2);
});


it('can create or get collections', function () {
    $this->client->createCollection('test_collection');

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
    $this->client->createCollection('test_collection');

    $collection = $this->client->getCollection('test_collection');

    expect($collection)
        ->toBeInstanceOf(CollectionResource::class)
        ->toHaveProperty('name', 'test_collection');
});


it('can modify a collection name or metadata', function () {
    $collection = $this->client->createCollection('test_collection', ['test' => 'test']);

    expect($collection->name)
        ->toBe('test_collection')
        ->and($collection->metadata)
        ->toMatchArray(['test' => 'test']);

    $collection->modify('test_collection_2', ['test' => 'test_2']);

    $collection = $this->client->getCollection('test_collection_2');

    expect($collection->name)
        ->toBe('test_collection_2')
        ->and($collection->metadata)
        ->toMatchArray(['test' => 'test_2']);

});

it('can delete a collection', function () {
    $this->client->createCollection('test_collection');

    $collection = $this->client->getCollection('test_collection');

    expect($collection)
        ->toBeInstanceOf(CollectionResource::class)
        ->toHaveProperty('name', 'test_collection');

    $this->client->deleteCollection('test_collection');

    $collection = $this->client->getCollection('test_collection');

    expect($collection)
        ->toBeNull();
});

it('can add single embeddings to a collection', function () {
    $collection = $this->client->createCollection('test_collection');

    $ids = ['test1'];
    $embeddings = [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
    $metadatas = [['test' => 'test']];
    $collection->add($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(1);
});

it('can upsert single embeddings to a collection', function () {
    $collection = $this->client->createCollection('test_collection');

    $ids = ['test1'];
    $embeddings = [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
    $metadatas = [['test' => 'test']];
    $collection->upsert($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(1);

    $collection->upsert($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(1);
});


it('can update single embeddings in a collection', function () {
    $collection = $this->client->createCollection('test_collection');

    $ids = ['test1'];
    $embeddings = [[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]];
    $metadatas = [['test' => 'test']];
    $collection->add($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(1);

    $collection->update($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(1);

    $collectionItems = $collection->get($ids);

    expect($collectionItems->ids)
        ->toMatchArray($ids)
        ->and($collectionItems->embeddings)
        ->toMatchArray($embeddings)
        ->and($collectionItems->metadatas)
        ->toMatchArray($metadatas);
});

it('can add batch embeddings to a collection', function () {
    $collection = $this->client->createCollection('test_collection');

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
    $collection->add($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(3);

    $getResponse = $collection->get($ids);

    expect($getResponse->ids)
        ->toMatchArray($ids)
        ->and($getResponse->embeddings)
        ->toMatchArray($embeddings)
        ->and($getResponse->metadatas)
        ->toMatchArray($metadatas);
});

it('can query a collection', function () {
    $collection = $this->client->createCollection('test_collection');

    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
        [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0],
        [10.0, 9.0, 8.0, 7.0, 6.0, 5.0, 4.0, 3.0, 2.0, 1.0],
    ];

    $collection->add($ids, $embeddings);

    expect($collection->count())->toBe(3);

    $queryResponse = $collection->query(
        queryEmbeddings: [
            [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
        ],
        nResults: 2
    );

    expect($queryResponse->ids[0])
        ->toMatchArray(['test1', 'test2']);

});

it('can peek a collection', function () {
    $collection = $this->client->createCollection('test_collection');

    $ids = ['test1', 'test2', 'test3'];
    $embeddings = [
        [1.0, 2.0, 3.0, 4.0, 5.0],
        [6.0, 7.0, 8.0, 9.0, 10.0],
        [11.0, 12.0, 13.0, 14.0, 15.0],
    ];

    $collection->add($ids, $embeddings);

    expect($collection->count())->toBe(3);

    $peekResponse = $collection->peek(2);

    expect($peekResponse->ids)
        ->toMatchArray(['test1', 'test2']);

});

it('can get a collection by id', function () {
    $collection = $this->client->createCollection('test_collection');

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

    $collection->add($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(3);

    $collectionItems = $collection->get(['test1', 'test2']);

    expect($collectionItems->ids)
        ->toMatchArray(['test1', 'test2'])
        ->and($collectionItems->embeddings)
        ->toMatchArray([
            [1.0, 2.0, 3.0, 4.0, 5.0],
            [6.0, 7.0, 8.0, 9.0, 10.0],
        ]);
});

it('can get a collection by where', function () {
    $collection = $this->client->createCollection('test_collection');

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

    $collection->add($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(3);

    $collectionItems = $collection->get(
        where: [
            'some' => ['$eq' => 'metadata1']
        ]
    );

    expect($collectionItems->ids)
        ->toHaveCount(1)
        ->and($collectionItems->ids[0])
        ->toBe('test1');
});

it('throws a value error when getting a collection by where with an invalid operator', function () {
    $collection = $this->client->createCollection('test_collection');

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

    $collection->add($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(3);

    $collectionItems = $collection->get(
        where: [
            'some' => ['$invalid' => 'metadata1']
        ]
    );
})->throws(ChromaValueException::class);

it('can delete a collection by id', function () {
    $collection = $this->client->createCollection('test_collection');

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

    $collection->add($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(3);

    $collection->delete(['test1', 'test2']);

    expect($collection->count())->toBe(1);
});

it('can delete a collection by where', function () {
    $collection = $this->client->createCollection('test_collection');

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

    $collection->add($ids, $embeddings, $metadatas);

    expect($collection->count())->toBe(3);

    $collection->delete(
        where: [
            'some' => 'metadata1'
        ]
    );

    expect($collection->count())->toBe(2);
});