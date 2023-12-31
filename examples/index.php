<?php

declare(strict_types=1);

require './vendor/autoload.php';

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Embeddings\JinaEmbeddingFunction;

$chroma = ChromaDB::factory()
    ->withDatabase('test_database')
    ->withTenant('test_tenant')
    ->connect();

$chroma->deleteAllCollections();

$embeddingFunction = new JinaEmbeddingFunction(
    'jina_8cbaafb9543e42f1a2fc7430d456c3faKKPc93W8Eur5T2XjAkryfwQ9TOv8'
);

$collection = $chroma->createCollection(
    name: 'test_collection',
    embeddingFunction: $embeddingFunction
);

$collection->add(
    ids: ['hello', 'world'],
    documents: ['This is a test document', 'The man is happy']
);

$queryResponse = $collection->query(
    queryTexts: ['The man is excited'],
    include: ['documents', 'distances']
);

dd($queryResponse->documents[0], $queryResponse->distances[0]);


