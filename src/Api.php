<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB;

use Codewithkyrian\ChromaDB\Exceptions\ConnectionException;
use Codewithkyrian\ChromaDB\Exceptions\ChromaException;
use Codewithkyrian\ChromaDB\Models\Collection;
use Codewithkyrian\ChromaDB\Models\Database;
use Codewithkyrian\ChromaDB\Models\Tenant;
use Codewithkyrian\ChromaDB\Requests\AddItemsRequest;
use Codewithkyrian\ChromaDB\Requests\CreateCollectionRequest;
use Codewithkyrian\ChromaDB\Requests\CreateDatabaseRequest;
use Codewithkyrian\ChromaDB\Requests\CreateTenantRequest;
use Codewithkyrian\ChromaDB\Requests\DeleteItemsRequest;
use Codewithkyrian\ChromaDB\Requests\ForkCollectionRequest;
use Codewithkyrian\ChromaDB\Requests\GetEmbeddingRequest;
use Codewithkyrian\ChromaDB\Requests\QueryItemsRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateCollectionRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateItemsRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateTenantRequest;
use Codewithkyrian\ChromaDB\Responses\GetItemsResponse;
use Codewithkyrian\ChromaDB\Responses\QueryItemsResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Client for ChromaDB API
 */
class Api
{
    public function __construct(
        public readonly ClientInterface $client,
        public readonly RequestFactoryInterface $requestFactory,
        public readonly StreamFactoryInterface $streamFactory,
        public readonly string $baseUri,
        public readonly array $headers = [],
    ) {}

    /**
     * Retrieves the current user's identity, tenant, and databases.
     */
    public function getUserIdentity(): array
    {
        $response = $this->sendRequest('GET', '/api/v2/auth/identity');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Retrieves a collection by Chroma Resource Name (CRN).
     *
     * @param string $crn The Chroma Resource Name of the collection.
     * @param string $database The database name.
     * @param string $tenant The tenant name.
     */
    public function getCollectionByCrn(string $crn, string $database, string $tenant): Collection
    {
        $response = $this->sendRequest('GET', "/api/v2/collections/{$crn}");

        return Collection::fromArray(json_decode($response->getBody()->getContents(), true), $this, $database, $tenant);
    }

    /**
     * Returns the health of the server and executor.
     */
    public function healthcheck(): array
    {
        $response = $this->sendRequest('GET', '/api/v2/healthcheck');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Returns the current time in nanoseconds since epoch.
     */
    public function heartbeat(): array
    {
        $response = $this->sendRequest('GET', '/api/v2/heartbeat');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Returns basic readiness information about the server.
     */
    public function preFlightChecks(): mixed
    {
        $response = $this->sendRequest('GET', '/api/v2/pre-flight-checks');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Resets the database and all collections (only authorized users can reset the database)
     */
    public function reset(): bool
    {
        $response = $this->sendRequest('POST', '/api/v2/reset');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Returns the version of the ChromaDB server.
     */
    public function version(): string
    {
        $response = $this->sendRequest('GET', '/api/v2/version');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Creates a new tenant.
     */
    public function createTenant(CreateTenantRequest $request): void
    {
        $this->sendRequest('POST', '/api/v2/tenants', [
            'json' => $request->toArray(),
        ]);
    }

    /**
     * Retrieves a tenant by name.
     */
    public function getTenant(string $tenant): ?Tenant
    {
        $response = $this->sendRequest('GET', "/api/v2/tenants/$tenant");

        $result = json_decode($response->getBody()->getContents(), true);

        return Tenant::fromArray($result);
    }

    /**
     * Updates a tenant.
     */
    public function updateTenant(string $tenant, UpdateTenantRequest $request): void
    {
        $this->sendRequest('PATCH', "/api/v2/tenants/$tenant", [
            'json' => $request->toArray(),
        ]);
    }

    /**
     * Creates a new database for a given tenant.
     * 
     * @param string $tenant Tenant ID to associate with the new database
     * @param CreateDatabaseRequest $request The request to create the database.
     */
    public function createDatabase(string $tenant, CreateDatabaseRequest $request): void
    {
        $this->sendRequest('POST', "/api/v2/tenants/$tenant/databases", [
            'json' => $request->toArray()
        ]);
    }

    /**
     * Lists all databases for a tenant.
     * 
     * @param string $tenant The tenant ID to list databases for.
     * @param int $limit Optional limit on the number of databases to return.
     * @param int $offset Optional offset on the number of databases to return.
     * 
     * @return Database[]
     */
    public function listDatabases(string $tenant, ?int $limit = null, ?int $offset = null): array
    {
        $response = $this->sendRequest('GET', "/api/v2/tenants/$tenant/databases", [
            'query' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return array_map(fn(array $item) => Database::fromArray($item), $result);
    }

    /**
     * Retrieves a database by name.
     * 
     * @param string $database The database name to retrieve.
     * @param string $tenant The tenant ID to retrieve the database from.
     * 
     * @return Database
     */
    public function getDatabase(string $database, string $tenant): Database
    {
        $response = $this->sendRequest('GET', "/api/v2/tenants/$tenant/databases/$database");

        $result = json_decode($response->getBody()->getContents(), true);

        return Database::fromArray($result);
    }

    /**
     * Deletes a database by name.
     * 
     * @param string $database The database name to delete.
     * @param string $tenant The tenant ID to delete the database from.
     */
    public function deleteDatabase(string $database, string $tenant): void
    {
        $this->sendRequest('DELETE', "/api/v2/tenants/$tenant/databases/$database");
    }

    /**
     * Lists all collections in the specified database.
     * 
     * @param string $database The database name to list collections for.
     * @param string $tenant The tenant ID to list collections for.
     * @param int $limit Optional limit on the number of collections to return.
     * @param int $offset Optional offset on the number of collections to return.
     * 
     * @return Collection[]
     */
    public function listCollections(string $database, string $tenant, ?int $limit = null, ?int $offset = null): array
    {
        $response = $this->sendRequest('GET', "/api/v2/tenants/$tenant/databases/$database/collections", [
            'query' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return array_map(fn(array $item) => Collection::fromArray($item, $this, $database, $tenant), $result);
    }

    /**
     * Creates a new collection under the specified database.
     * 
     * @param string $database The database name to create the collection for.
     * @param string $tenant The tenant ID to create the collection for.
     * @param CreateCollectionRequest $request The request to create the collection.
     * 
     * @return Collection
     */
    public function createCollection(string $database, string $tenant, CreateCollectionRequest $request): Collection
    {
        $response = $this->sendRequest('POST', "/api/v2/tenants/$tenant/databases/$database/collections", [
            'json' => $request->toArray()
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return Collection::fromArray($result, $this, $database, $tenant);
    }

    /**
     * Retrieves a collection by ID or name.
     * 
     * @param string $collectionId The UUID of the collection to retrieve.
     * @param string $database The database name to retrieve the collection from.
     * @param string $tenant The tenant ID to retrieve the collection from.
     * 
     * @return Collection
     */
    public function getCollection(string $collectionId, string $database, string $tenant): Collection
    {
        $response = $this->sendRequest('GET', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId");

        $result = json_decode($response->getBody()->getContents(), true);

        return Collection::fromArray($result, $this, $database, $tenant);
    }

    /**
     * Updates an existing collection's name or metadata.
     * 
     * @param string $collectionId The UUID of the collection to update.
     * @param string $database The database name to update the collection in.
     * @param string $tenant The tenant ID to update the collection in.
     * @param UpdateCollectionRequest $request The request to update the collection.
     */
    public function updateCollection(string $collectionId, string $database, string $tenant, UpdateCollectionRequest $request): void
    {
        $this->sendRequest('PUT', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId", [
            'json' => $request->toArray(),
        ]);
    }

    /**
     * Forks an existing collection.
     * 
     * @param string $collectionId The UUID of the collection to fork.
     * @param string $database The database name to fork the collection from.
     * @param string $tenant The tenant ID to fork the collection from.
     * @param ForkCollectionRequest $request The request to fork the collection.
     * 
     * @return Collection
     */
    public function forkCollection(string $collectionId, string $database, string $tenant, ForkCollectionRequest $request): Collection
    {
        $response = $this->sendRequest('POST', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/fork", [
            'json' => $request->toArray()
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return Collection::fromArray($result, $this, $database, $tenant);
    }

    /**
     * Deletes a collection in a given database.
     * 
     * @param string $collectionId The UUID of the collection to delete.
     * @param string $database The database name to delete the collection from.
     * @param string $tenant The tenant ID to delete the collection from.
     */
    public function deleteCollection(string $collectionId, string $database, string $tenant): void
    {
        $this->sendRequest('DELETE', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId");
    }

    /**
     * Retrieves the total number of collections in a given database.
     * 
     * @param string $database The database name to count collections in.
     * @param string $tenant The tenant ID to count collections in.
     * 
     * @return int
     */
    public function countCollections(string $database, string $tenant): int
    {
        $response = $this->sendRequest('GET', "/api/v2/tenants/$tenant/databases/$database/collections_count");

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Adds items to a collection.
     * 
     * @param string $collectionId The UUID of the collection to add items to.
     * @param string $database The database name to add items to.
     * @param string $tenant The tenant ID to add items to.
     * @param AddItemsRequest $request The request to add items to the collection.
     */
    public function addCollectionItems(string $collectionId, string $database, string $tenant, AddItemsRequest $request): void
    {
        $this->sendRequest('POST', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/add", [
            'json' => $request->toArray(),
        ]);
    }

    /**
     * Retrieves the number of items in a collection.
     * 
     * @param string $collectionId The UUID of the collection to count items for.
     * @param string $database The database name to count items in.
     * @param string $tenant The tenant ID to count items in.
     * 
     * @return int
     */
    public function countCollectionItems(string $collectionId, string $database, string $tenant): int
    {
        $response = $this->sendRequest('GET', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/count");

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Updates items in a collection.
     * 
     * @param string $collectionId The UUID of the collection to update items in.
     * @param string $database The database name to update items in.
     * @param string $tenant The tenant ID to update items in.
     * @param UpdateItemsRequest $request The request to update items in the collection.
     */
    public function updateCollectionItems(string $collectionId, string $database, string $tenant, UpdateItemsRequest $request): void
    {
        $this->sendRequest('POST', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/update", [
            'json' => $request->toArray(),
        ]);
    }

    /**
     * Upserts items in a collection (create if not exists, otherwise update).
     * 
     * @param string $collectionId The UUID of the collection to upsert items in.
     * @param string $database The database name to upsert items in.
     * @param string $tenant The tenant ID to upsert items in.
     * @param AddItemsRequest $request The request to upsert items in the collection.
     */
    public function upsertCollectionItems(string $collectionId, string $database, string $tenant, AddItemsRequest $request): void
    {
        $this->sendRequest('POST', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/upsert", [
            'json' => $request->toArray(),
        ]);
    }

    /**
     * Retrieves items from a collection by ID or metadata filter.
     * 
     * @param string $collectionId The UUID of the collection to get items from.
     * @param string $database The database name to get items from.
     * @param string $tenant The tenant ID to get items from.
     * @param GetEmbeddingRequest $request The request to get items from the collection.
     * 
     * @return GetItemsResponse
     */
    public function getCollectionItems(string $collectionId, string $database, string $tenant, GetEmbeddingRequest $request): GetItemsResponse
    {
        $response = $this->sendRequest('POST', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/get", [
            'json' => $request->toArray(),
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return GetItemsResponse::fromArray($result);
    }

    /**
     * Deletes items from a collection by ID or metadata filter.
     * 
     * @param string $collectionId The UUID of the collection to delete items from.
     * @param string $database The database name to delete items from.
     * @param string $tenant The tenant ID to delete items from.
     * @param DeleteItemsRequest $request The request to delete items from the collection.
     */
    public function deleteCollectionItems(string $collectionId, string $database, string $tenant, DeleteItemsRequest $request): void
    {
        $this->sendRequest('POST', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/delete", [
            'json' => $request->toArray(),
        ]);
    }

    /**
     * Query a collection in a variety of ways, including vector search, metadata filtering, and full-text search
     * 
     * @param string $collectionId The UUID of the collection to query.
     * @param string $database The database name to query the collection in.
     * @param string $tenant The tenant ID to query the collection in.
     * @param QueryItemsRequest $request The request to query the collection.
     * 
     * @return QueryItemsResponse
     */
    public function queryCollectionItems(string $collectionId, string $database, string $tenant, QueryItemsRequest $request): QueryItemsResponse
    {
        $response = $this->sendRequest('POST', "/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/query", [
            'json' => $request->toArray(),
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return QueryItemsResponse::fromArray($result);
    }

    private function sendRequest(string $method, string $path, array $options = []): ResponseInterface
    {
        $uri = $this->baseUri . $path;
        if (isset($options['query'])) {
            $uri .= '?' . http_build_query($options['query']);
        }

        $request = $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json');

        foreach ($this->headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (isset($options['json'])) {
            $body = $this->streamFactory->createStream(json_encode($options['json']));
            $request = $request->withBody($body);
        }

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode());
        }

        if ($response->getStatusCode() >= 400) {
            $this->handleErrorResponse($response);
        }

        return $response;
    }

    private function handleErrorResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        $errorType = $body['error'] ?? 'UnknownError';
        $message = $body['message'] ?? 'Unknown error occurred';

        if ($statusCode === 409) {
            $errorType = 'UniqueConstraintError';
        }

        throw ChromaException::create($message, $errorType, $statusCode);
    }
}
