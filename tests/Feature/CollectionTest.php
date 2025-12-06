<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Tests\Feature;

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Codewithkyrian\ChromaDB\Exceptions\ChromaException;
use Codewithkyrian\ChromaDB\Exceptions\ChromaInvalidArgumentException;
use Codewithkyrian\ChromaDB\Types\Includes;
use Codewithkyrian\ChromaDB\Types\Record;
use Codewithkyrian\ChromaDB\Types\ScoredRecord;

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
        name: 'collection_ops_test',
        embeddingFunction: $this->embeddingFunction
    );
});

afterEach(function () {
    $this->client->reset();
});

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

it('can add items to collection using record objects', function () {
    $records = [
        Record::make('1')
            ->withEmbedding([1.0, 2.0, 3.0, 4.0, 5.0])
            ->withMetadata(['test' => 'creation']),
    ];

    $this->collection->add($records);

    $item = $this->collection->get(ids: ['1']);
    expect($item->ids)->toBe(['1'])
        ->and($item->metadatas[0])->toBe(['test' => 'creation']);
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

it('can update items in collection using record objects', function () {
    $this->collection->add([
        Record::make('1')
            ->withEmbedding([1.0, 2.0, 3.0, 4.0, 5.0])
            ->withMetadata(['v' => 1]),
    ]);

    $this->collection->update([
        Record::make('1')
            ->withEmbedding([1.0, 2.0, 3.0, 4.0, 5.0])
            ->withMetadata(['v' => 2]),
    ]);

    $item = $this->collection->get(ids: ['1']);
    expect($item->metadatas[0])->toBe(['v' => 2]);
});

it('can upsert items in collection using record objects', function () {
    $this->collection->add([
        Record::make('1')
            ->withEmbedding([1.0, 2.0, 3.0, 4.0, 5.0])
            ->withMetadata(['v' => 1]),
    ]);

    $this->collection->upsert([
        Record::make('1')
            ->withEmbedding([1.0, 2.0, 3.0, 4.0, 5.0])
            ->withMetadata(['v' => 5]), // Update
        Record::make('2')
            ->withEmbedding([6.0, 7.0, 8.0, 9.0, 10.0])
            ->withMetadata(['v' => 10]), // Insert
    ]);

    $res = $this->collection->get();
    expect($res->ids)->toHaveCount(2)
        ->and($this->collection->get(['1'])->metadatas[0])->toBe(['v' => 5]);
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

it('can retrieve items as record objects', function () {
    $this->collection->add([
        Record::make('1')
            ->withEmbedding([1.0, 2.0, 3.0, 4.0, 5.0])
            ->withDocument('test doc'),
    ]);

    $response = $this->collection->get(
        ids: ['1'],
        include: [Includes::Documents, Includes::Embeddings]
    );

    $records = $response->asRecords();

    expect($records)->toHaveCount(1)
        ->and($records[0])->toBeInstanceOf(Record::class)
        ->and($records[0]->id)->toBe('1')
        ->and($records[0]->document)->toBe('test doc');
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

it('can retrieve query results as record objects', function () {
    $this->collection->add([
        Record::make('1')->withEmbedding([1.0, 2.0, 3.0, 4.0, 5.0]),
    ]);

    $response = $this->collection->query(
        queryEmbeddings: [[1.0, 2.0, 3.0, 4.0, 5.0]],
        nResults: 1
    );

    $records = $response->asRecords();

    expect($records)->toHaveCount(1)
        ->and($records[0][0])->toBeInstanceOf(ScoredRecord::class)
        ->and($records[0][0]->id)->toBe('1')
        ->and($records[0][0]->distance)->toBeLessThan(0.001);
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

