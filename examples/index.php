<?php

declare(strict_types=1);

require './vendor/autoload.php';

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Embeddings\JinaEmbeddingFunction;
use Codewithkyrian\ChromaDB\Embeddings\OllamaEmbeddingFunction;

$chroma = ChromaDB::factory()
    ->withDatabase('test_database')
    ->withTenant('test_tenant')
    ->connect();

$chroma->deleteAllCollections();

$embeddingFunction = new OllamaEmbeddingFunction();

$collection = $chroma->createCollection(
    name: 'test_collection',
    embeddingFunction: $embeddingFunction
);


$collection->add(
    ids: ['1', '2', '3'],
    documents: ['He seems very happy', 'He was very sad when we last talked', 'She made him angry']
);

$queryResponse = $collection->query(
    queryTexts: ['She annoyed him'],
    include: ['documents', 'distances']
);

dd($queryResponse->documents[0], $queryResponse->distances[0]);


