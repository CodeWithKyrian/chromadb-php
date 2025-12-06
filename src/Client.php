<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB;

use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Codewithkyrian\ChromaDB\Api;
use Codewithkyrian\ChromaDB\Exceptions\NotFoundException;
use Codewithkyrian\ChromaDB\Models\Collection;
use Codewithkyrian\ChromaDB\Requests\CreateDatabaseRequest;
use Codewithkyrian\ChromaDB\Requests\CreateTenantRequest;
use Codewithkyrian\ChromaDB\Requests\CreateCollectionRequest;
use Codewithkyrian\ChromaDB\Requests\ForkCollectionRequest;

class Client
{
    public function __construct(
        public readonly Api $api,
        public readonly string $database,
        public readonly string $tenant,
    ) {
        $this->initDatabaseAndTenant();
    }

    public function initDatabaseAndTenant(): void
    {
        try {
            $this->api->getTenant($this->tenant);
        } catch (NotFoundException) {
            $createTenantRequest = new CreateTenantRequest($this->tenant);
            $this->api->createTenant($createTenantRequest);
        }

        try {
            $this->api->getDatabase($this->database, $this->tenant);
        } catch (NotFoundException) {
            $createDatabaseRequest = new CreateDatabaseRequest($this->database);
            $this->api->createDatabase($this->tenant, $createDatabaseRequest);
        }
    }

    /**
     * Returns the version of the Chroma API.
     */
    public function version(): string
    {
        return $this->api->version();
    }

    /**
     * Returns the current time in nanoseconds since epoch. This is useful for
     * checking if the server is alive.
     */
    public function heartbeat(): int
    {
        $res = $this->api->heartbeat();

        return $res['nanosecond heartbeat'] ?? 0;
    }

    /**
     * Lists all collections.
     *
     * @return Collection[]
     */
    public function listCollections(): array
    {
        return $this->api->listCollections($this->database, $this->tenant);
    }


    /**
     * Creates a new collection with the specified properties.
     *
     * @param string $name The name of the collection.
     * @param ?array $metadata Optional metadata associated with the collection.
     * @param ?EmbeddingFunction $embeddingFunction Optional custom embedding function for the collection.
     *
     * @return Collection
     */
    public function createCollection(string $name, ?array $metadata = null, ?EmbeddingFunction $embeddingFunction = null): Collection
    {
        $request = new CreateCollectionRequest($name, $metadata);

        $collection = $this->api->createCollection($this->database, $this->tenant, $request);

        if ($embeddingFunction) {
            $collection->setEmbeddingFunction($embeddingFunction);
        }

        return $collection;
    }

    /**
     * Gets or creates a collection with the specified properties.
     *
     * @param string $name The name of the collection.
     * @param ?array $metadata Optional metadata associated with the collection.
     * @param ?EmbeddingFunction $embeddingFunction Optional custom embedding function for the collection.
     *
     * @return Collection
     */
    public function getOrCreateCollection(string $name, ?array $metadata = null, ?EmbeddingFunction $embeddingFunction = null): Collection
    {
        $request = new CreateCollectionRequest($name, $metadata, true);

        $collection = $this->api->createCollection($this->database, $this->tenant, $request);

        if ($embeddingFunction) {
            $collection->setEmbeddingFunction($embeddingFunction);
        }

        return $collection;
    }

    /**
     * Gets a collection with the specified name. Will raise an exception if the
     * collection does not exist.
     *
     * @param string $name The name of the collection.
     * @param ?EmbeddingFunction $embeddingFunction Optional custom embedding function for the collection.
     *
     * @return Collection
     */
    public function getCollection(string $name, ?EmbeddingFunction $embeddingFunction = null): Collection
    {
        $collection = $this->api->getCollection($name, $this->database, $this->tenant);

        if ($embeddingFunction) {
            $collection->setEmbeddingFunction($embeddingFunction);
        }

        return $collection;
    }

    /**
     * Forks an existing collection.
     *
     * @param string $name The name of the collection to fork.
     * @param string $newName The name for the forked collection.
     * @param ?EmbeddingFunction $embeddingFunction Optional custom embedding function for the forked collection.
     *
     * @return Collection
     */
    public function forkCollection(string $name, string $newName, ?EmbeddingFunction $embeddingFunction = null): Collection
    {
        $collection = $this->api->getCollection($name, $this->database, $this->tenant);
        $request = new ForkCollectionRequest($newName);

        $forkedCollection = $this->api->forkCollection($collection->id, $this->database, $this->tenant, $request);

        if ($embeddingFunction) {
            $forkedCollection->setEmbeddingFunction($embeddingFunction);
        }

        return $forkedCollection;
    }

    /**
     * Deletes a collection with the specified name.
     *
     * @param string $name The name of the collection.
     */
    public function deleteCollection(string $name): void
    {
        $this->api->deleteCollection($name, $this->database, $this->tenant);
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

    public function reset(): bool
    {
        return $this->api->reset();
    }
}
