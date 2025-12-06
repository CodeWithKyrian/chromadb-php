<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Tests\Feature;
use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Codewithkyrian\ChromaDB\Exceptions\NotFoundException;
use Codewithkyrian\ChromaDB\Models\Collection;

beforeEach(function () {
    $this->client = ChromaDB::factory()
        ->withHeader('X-Chroma-Token', 'test-token')
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

afterEach(function () {
    $this->client->reset();
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
        ->toBeInstanceOf(Collection::class)
        ->toHaveProperty('name', 'test_collection');

    $collection = $this->client->getOrCreateCollection('test_collection_2');

    expect($collection)
        ->toBeInstanceOf(Collection::class)
        ->toHaveProperty('name', 'test_collection_2');
});

it('can get a collection', function () {
    $collection = $this->client->getCollection('test_collection');

    expect($collection)
        ->toBeInstanceOf(Collection::class)
        ->toHaveProperty('name', 'test_collection');
});

it('cannot get a collection that does not exist', function () {
    $this->client->getCollection('test_collection_2');
})->throws(NotFoundException::class);

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
        ->toThrow(NotFoundException::class);
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

it('cannot delete a collection that does not exist', function () {
    $this->client->deleteCollection('test_collection_2');
})->throws(NotFoundException::class);
