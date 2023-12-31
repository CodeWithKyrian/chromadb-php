<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB;

use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Codewithkyrian\ChromaDB\Generated\ChromaApiClient;
use Codewithkyrian\ChromaDB\Generated\Exceptions\ChromaNotFoundException;
use Codewithkyrian\ChromaDB\Generated\Models\Collection;
use Codewithkyrian\ChromaDB\Resources\CollectionResource;

class Client
{
    public function __construct(
        public readonly ChromaApiClient $apiClient,
        public readonly string          $database,
        public readonly string          $tenant,
    )
    {
        $this->initDatabaseAndTenant();
    }


    public function initDatabaseAndTenant(): void
    {
        try {
            $this->apiClient->getTenant($this->tenant);
        } catch (ChromaNotFoundException) {
            $createTenantRequest = new Generated\Requests\CreateTenantRequest($this->tenant);
            $this->apiClient->createTenant($createTenantRequest);
        }

        try {
            $this->apiClient->getDatabase($this->database, $this->tenant);
        } catch (ChromaNotFoundException) {
            $createDatabaseRequest = new Generated\Requests\CreateDatabaseRequest($this->database);
            $this->apiClient->createDatabase($this->tenant, $createDatabaseRequest);
        }
    }

    /**
     * Returns the version of the Chroma API.
     */
    public function version(): string
    {
        return $this->apiClient->version();
    }

    /**
     * Returns the current time in nanoseconds since epoch. This is useful for
     * checking if the server is alive.
     */
    public function heartbeat(): int
    {
        $res = $this->apiClient->heartbeat();

        return $res['nanosecond heartbeat'] ?? 0;
    }

    /**
     * Lists all collections.
     *
     * @return Collection[]
     */
    public function listCollections(): array
    {
        return  $this->apiClient->listCollections($this->database, $this->tenant);
    }


    /**
     * Creates a new collection with the specified properties.
     *
     * @param string $name The name of the collection.
     * @param ?array $metadata Optional metadata associated with the collection.
     * @param ?EmbeddingFunction $embeddingFunction Optional custom embedding function for the collection.
     *
     * @return CollectionResource
     */
    public function createCollection(string $name, ?array $metadata = null, ?EmbeddingFunction $embeddingFunction = null): CollectionResource
    {
        $request = new Generated\Requests\CreateCollectionRequest($name, $metadata);

        $collection = $this->apiClient->createCollection($this->database, $this->tenant, $request);


        return CollectionResource::make(
            $collection,
            $this->database,
            $this->tenant,
            $embeddingFunction,
            $this->apiClient
        );
    }

    /**
     * Gets or creates a collection with the specified properties.
     *
     * @param string $name The name of the collection.
     * @param ?array $metadata Optional metadata associated with the collection.
     * @param ?EmbeddingFunction $embeddingFunction Optional custom embedding function for the collection.
     *
     * @return CollectionResource
     */
    public function getOrCreateCollection(string $name, ?array $metadata = null, ?EmbeddingFunction $embeddingFunction = null): CollectionResource
    {
        $request = new Generated\Requests\CreateCollectionRequest($name, $metadata, true);

        $collection = $this->apiClient->createCollection($this->database, $this->tenant, $request);

        return CollectionResource::make(
            $collection,
            $this->database,
            $this->tenant,
            $embeddingFunction,
            $this->apiClient
        );
    }

    /**
     * Gets a collection with the specified name. Will raise an exception if the
     * collection does not exist.
     *
     * @param string $name The name of the collection.
     * @param ?EmbeddingFunction $embeddingFunction Optional custom embedding function for the collection.
     *
     * @return CollectionResource
     */
    public function getCollection(string $name, ?EmbeddingFunction $embeddingFunction = null): CollectionResource
    {
        $collection = $this->apiClient->getCollection($name, $this->database, $this->tenant);

        return CollectionResource::make(
            $collection,
            $this->database,
            $this->tenant,
            $embeddingFunction,
            $this->apiClient
        );
    }

    /**
     * Deletes a collection with the specified name.
     *
     * @param string $name The name of the collection.
     */
    public function deleteCollection(string $name): void
    {
        $this->apiClient->deleteCollection($name, $this->database, $this->tenant);
    }

    /**
     * De
     */
    public function deleteAllCollections(): void
    {
        $collections = $this->listCollections();

        foreach ($collections as $collection) {
            $this->deleteCollection($collection->name);
        }
    }



}