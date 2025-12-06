<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Embeddings\JinaEmbeddingFunction;
use Codewithkyrian\ChromaDB\Embeddings\OllamaEmbeddingFunction;

$chroma = ChromaDB::factory()
    ->withDatabase('test_database')
    ->withTenant('test_tenant')
    ->connect();

$embeddingFunction = new OllamaEmbeddingFunction();

$collection = $chroma->getCollection(
    name: 'test_collection',
    embeddingFunction: $embeddingFunction
);

$items = [
    ["id" => 1, "content" => "He seems very happy"],
    ["id" => 2, "content" => "He was very sad when we last talked"],
    ["id" => 3, "content" => "She made him angry"],
];

$collection->add(
    ids: array_column($items, 'id'),
    documents: array_column($items, 'content')
);

$queryResponse = $collection->query(
    queryTexts: ['She annoyed him'],
    include: ['documents', 'distances']
);

dd($queryResponse->documents[0], $queryResponse->distances[0]);
