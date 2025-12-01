<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Tests\Feature;

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Requests\AddItemsRequest;
use Codewithkyrian\ChromaDB\Requests\CreateCollectionRequest;
use Codewithkyrian\ChromaDB\Requests\CreateDatabaseRequest;
use Codewithkyrian\ChromaDB\Requests\CreateTenantRequest;
use Codewithkyrian\ChromaDB\Requests\DeleteItemsRequest;
use Codewithkyrian\ChromaDB\Requests\GetEmbeddingRequest;
use Codewithkyrian\ChromaDB\Requests\QueryItemsRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateCollectionRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateItemsRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateTenantRequest;

beforeEach(function () {
    $this->api = ChromaDB::factory()
        ->withHeader('X-Chroma-Token', 'test-token')
        ->createApi();
});

afterEach(function () {
    $this->api->reset();
});

it('can get user identity', function () {
    $identity = $this->api->getUserIdentity();
    
    expect($identity)->toBeArray()
        ->and($identity)->toHaveKey('user_id')
        ->and($identity)->toHaveKey('tenant');
});

it('can check health', function () {
    $health = $this->api->healthcheck();
    
    expect($health)->toBeArray();
});

it('can check heartbeat', function () {
    $heartbeat = $this->api->heartbeat();
    
    expect($heartbeat)->toBeArray()
        ->and($heartbeat)->toHaveKey('nanosecond heartbeat');
});

it('can check pre-flight checks', function () {
    $checks = $this->api->preFlightChecks();
    
    expect($checks)->toBeArray();
});

it('can get version', function () {
    $version = $this->api->version();
    
    expect($version)->toBeString();
});

it('can create a tenant', function () {
    $tenantName = 'test-tenant-' . uniqid();
    $this->api->createTenant(new CreateTenantRequest($tenantName));
    
    $tenant = $this->api->getTenant($tenantName);
    
    expect($tenant->name)->toBe($tenantName);
});

it('can get a tenant', function () {
    $tenantName = 'test-tenant-' . uniqid();
    $this->api->createTenant(new CreateTenantRequest($tenantName));
    
    $tenant = $this->api->getTenant($tenantName);
    
    expect($tenant->name)->toBe($tenantName);
});

it('can create a database', function () {
    $dbName = 'test-db-' . uniqid();
    $this->api->createDatabase('default_tenant', new CreateDatabaseRequest($dbName));
    
    $database = $this->api->getDatabase($dbName, 'default_tenant');
    expect($database->name)->toBe($dbName);
});

it('can list databases', function () {
    $dbName = 'test-db-' . uniqid();
    $this->api->createDatabase('default_tenant', new CreateDatabaseRequest($dbName));
    
    $databases = $this->api->listDatabases('default_tenant');
    expect($databases)->toBeArray();
});

it('can get a database', function () {
    $dbName = 'test-db-' . uniqid();
    $this->api->createDatabase('default_tenant', new CreateDatabaseRequest($dbName));
    
    $database = $this->api->getDatabase($dbName, 'default_tenant');
    expect($database->name)->toBe($dbName);
});

it('can delete a database', function () {
    $dbName = 'test-db-' . uniqid();
    $this->api->createDatabase('default_tenant', new CreateDatabaseRequest($dbName));
    
    $this->api->deleteDatabase($dbName, 'default_tenant');
    
    $databases = $this->api->listDatabases('default_tenant');
    $names = array_map(fn($db) => $db->name, $databases);
    expect($names)->not->toContain($dbName);
});

it('can create a collection', function () {
    $collectionName = 'test-collection-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    expect($collection->name)->toBe($collectionName);
});

it('can list collections', function () {
    $collectionName = 'test-collection-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $collections = $this->api->listCollections('default_database', 'default_tenant');
    expect($collections)->toBeArray();
});

it('can get a collection', function () {
    $collectionName = 'test-collection-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $fetchedCollection = $this->api->getCollection($collectionName, 'default_database', 'default_tenant');
    expect($fetchedCollection->id)->toBe($collection->id);
});

it('can update a collection', function () {
    $collectionName = 'test-collection-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $collections = $this->api->listCollections('default_database', 'default_tenant');
    $ids = array_map(fn($c) => $c->id, $collections);
    expect($ids)->toContain($collection->id);

    $updatedName = 'updated-' . $collectionName;
    $this->api->updateCollection($collection->id, 'default_database', 'default_tenant', new UpdateCollectionRequest($updatedName, ['new' => 'metadata']));
    
    $updatedCollection = $this->api->getCollection($updatedName, 'default_database', 'default_tenant');
    expect($updatedCollection->name)->toBe($updatedName)
        ->and($updatedCollection->metadata)->toBe(['new' => 'metadata']);
});

it('can delete a collection', function () {
    $collectionName = 'test-collection-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $this->api->deleteCollection($collectionName, 'default_database', 'default_tenant');
    
    $collections = $this->api->listCollections('default_database', 'default_tenant');
    $ids = array_map(fn($c) => $c->id, $collections);
    expect($ids)->not->toContain($collection->id);
});

it('can count collections', function () {
    $initialCount = $this->api->countCollections('default_database', 'default_tenant');
    
    $collectionName1 = 'test-collection-' . uniqid();
    $collectionName2 = 'test-collection-' . uniqid();
    
    $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName1, null));
    $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName2, null));
    
    $newCount = $this->api->countCollections('default_database', 'default_tenant');
    
    expect($newCount)->toBe($initialCount + 2);
});

it('can add items to a collection', function () {
    $collectionName = 'test-items-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $this->api->addCollectionItems($collection->id, 'default_database', 'default_tenant', new AddItemsRequest(
        ids: ['id1', 'id2'],
        embeddings: [[1.1, 2.2], [3.3, 4.4]],
        metadatas: [['key' => 'value1'], ['key' => 'value2']],
        documents: ['doc1', 'doc2'],
        images: null
    ));
    
    $count = $this->api->countCollectionItems($collection->id, 'default_database', 'default_tenant');
    expect($count)->toBe(2);
});

it('can count items in a collection', function () {
    $collectionName = 'test-items-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $this->api->addCollectionItems($collection->id, 'default_database', 'default_tenant', new AddItemsRequest(
        ids: ['id1'],
        embeddings: [[1.1, 2.2]],
        metadatas: [['key' => 'value1']],
        documents: ['doc1'],
        images: null
    ));
    
    $count = $this->api->countCollectionItems($collection->id, 'default_database', 'default_tenant');
    expect($count)->toBe(1);
});

it('can get items from a collection', function () {
    $collectionName = 'test-items-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $this->api->addCollectionItems($collection->id, 'default_database', 'default_tenant', new AddItemsRequest(
        ids: ['id1', 'id2'],
        embeddings: [[1.1, 2.2], [3.3, 4.4]],
        metadatas: [['key' => 'value1'], ['key' => 'value2']],
        documents: ['doc1', 'doc2'],
        images: null
    ));
    
    $items = $this->api->getCollectionItems($collection->id, 'default_database', 'default_tenant', new GetEmbeddingRequest(
        ids: ['id1'],
        where: null,
        whereDocument: null,
        sort: null,
        limit: null,
        offset: null,
        include: []
    ));
    expect($items->ids)->toContain('id1')
        ->and($items->ids)->not->toContain('id2');
});

it('can query items in a collection', function () {
    $collectionName = 'test-items-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $this->api->addCollectionItems($collection->id, 'default_database', 'default_tenant', new AddItemsRequest(
        ids: ['id1'],
        embeddings: [[1.1, 2.2]],
        metadatas: [['key' => 'value1']],
        documents: ['doc1'],
        images: null
    ));
    
    $query = $this->api->queryCollectionItems($collection->id, 'default_database', 'default_tenant', new QueryItemsRequest(
        queryEmbeddings: [[1.1, 2.2]],
        nResults: 1,
        where: null,
        whereDocument: null,
        include: []
    ));
    expect($query->ids[0])->toContain('id1');
});

it('can update items in a collection', function () {
    $collectionName = 'test-items-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $this->api->addCollectionItems($collection->id, 'default_database', 'default_tenant', new AddItemsRequest(
        ids: ['id1'],
        embeddings: [[1.1, 2.2]],
        metadatas: [['key' => 'value1']],
        documents: ['doc1'],
        images: null
    ));
    
    $this->api->updateCollectionItems($collection->id, 'default_database', 'default_tenant', new UpdateItemsRequest(
        embeddings: [[1.2, 2.3]],
        ids: ['id1'],
        metadatas: [['key' => 'updated_value1']],
        documents: ['updated_doc1'],
        images: null
    ));
    $updatedItem = $this->api->getCollectionItems($collection->id, 'default_database', 'default_tenant', new GetEmbeddingRequest(ids: ['id1']));
    expect($updatedItem->metadatas[0])->toBe(['key' => 'updated_value1']);
});

it('can upsert items in a collection', function () {
    $collectionName = 'test-items-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $this->api->addCollectionItems($collection->id, 'default_database', 'default_tenant', new AddItemsRequest(
        ids: ['id1'],
        embeddings: [[1.1, 2.2]],
        metadatas: [['key' => 'value1']],
        documents: ['doc1'],
        images: null
    ));
    
    $this->api->upsertCollectionItems($collection->id, 'default_database', 'default_tenant', new AddItemsRequest(
        embeddings: [[1.3, 2.4], [5.5, 6.6]],
        metadatas: [['key' => 'upserted_value1'], ['key' => 'value3']],
        ids: ['id1', 'id3'],
        documents: ['upserted_doc1', 'doc3'],
        images: null
    ));
    $count = $this->api->countCollectionItems($collection->id, 'default_database', 'default_tenant');
    expect($count)->toBe(2);
});

it('can delete items from a collection', function () {
    $collectionName = 'test-items-' . uniqid();
    $collection = $this->api->createCollection('default_database', 'default_tenant', new CreateCollectionRequest($collectionName, null));
    
    $this->api->addCollectionItems($collection->id, 'default_database', 'default_tenant', new AddItemsRequest(
        ids: ['id1', 'id2'],
        embeddings: [[1.1, 2.2], [3.3, 4.4]],
        metadatas: [['key' => 'value1'], ['key' => 'value2']],
        documents: ['doc1', 'doc2'],
        images: null
    ));
    
    $this->api->deleteCollectionItems($collection->id, 'default_database', 'default_tenant', new DeleteItemsRequest(
        ids: ['id1'],
        where: null,
        whereDocument: null
    ));
    $count = $this->api->countCollectionItems($collection->id, 'default_database', 'default_tenant');
    expect($count)->toBe(1);
});

