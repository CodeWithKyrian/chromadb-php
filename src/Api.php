<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB;

use Codewithkyrian\ChromaDB\Exceptions\ChromaConnectionException;
use Codewithkyrian\ChromaDB\Exceptions\ChromaException;
use Codewithkyrian\ChromaDB\Models\Collection;
use Codewithkyrian\ChromaDB\Models\Database;
use Codewithkyrian\ChromaDB\Models\Tenant;
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
use Codewithkyrian\ChromaDB\Responses\GetItemsResponse;
use Codewithkyrian\ChromaDB\Responses\QueryItemsResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Client for ChromaDB API
 */
class Api
{
    public function __construct(
        public readonly Client $httpClient,
    ) {}

    /**
     * Retrieves the current user's identity, tenant, and databases.
     */
    public function getUserIdentity(): array
    {
        try {
            $response = $this->httpClient->get('/api/v2/auth/identity');
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

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
        try {
            $response = $this->httpClient->get("/api/v2/collections/{$crn}");
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        return Collection::make(json_decode($response->getBody()->getContents(), true), $this, $database, $tenant);
    }

    /**
     * Returns the health of the server and executor.
     */
    public function healthcheck(): array
    {
        try {
            $response = $this->httpClient->get('/api/v2/healthcheck');
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Returns the current time in nanoseconds since epoch.
     */
    public function heartbeat(): array
    {
        try {
            $response = $this->httpClient->get('/api/v2/heartbeat');
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Returns basic readiness information about the server.
     */
    public function preFlightChecks(): mixed
    {
        try {
            $response = $this->httpClient->get('/api/v2/pre-flight-checks');
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Resets the database and all collections (only authorized users can reset the database)
     */
    public function reset(): bool
    {
        try {
            $response = $this->httpClient->post('/api/v2/reset');
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Returns the version of the ChromaDB server.
     */
    public function version(): string
    {
        try {
            $response = $this->httpClient->get('/api/v2/version');
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Creates a new tenant.
     */
    public function createTenant(CreateTenantRequest $request): void
    {
        try {
            $this->httpClient->post('/api/v2/tenants', [
                'json' => $request->toArray(),
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
    }

    /**
     * Retrieves a tenant by name.
     */
    public function getTenant(string $tenant): ?Tenant
    {
        try {
            $response = $this->httpClient->get("/api/v2/tenants/$tenant");
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        return Tenant::make($result);
    }

    /**
     * Updates a tenant.
     */
    public function updateTenant(string $tenant, UpdateTenantRequest $request): void
    {
        try {
            $this->httpClient->put("/api/v2/tenants/$tenant", [
                'json' => $request->toArray(),
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
    }

    /**
     * Creates a new database for a given tenant.
     * 
     * @param string $tenant Tenant ID to associate with the new database
     * @param CreateDatabaseRequest $request The request to create the database.
     */
    public function createDatabase(string $tenant, CreateDatabaseRequest $request): void
    {
        try {
            $this->httpClient->post("/api/v2/tenants/$tenant/databases", [
                'json' => $request->toArray()
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
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
        try {
            $response = $this->httpClient->get("/api/v2/tenants/$tenant/databases", [
                'query' => [
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        return array_map(fn(array $item) => Database::make($item), $result);
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
        try {
            $response = $this->httpClient->get("/api/v2/tenants/$tenant/databases/$database");
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        return Database::make($result);
    }

    /**
     * Deletes a database by name.
     * 
     * @param string $database The database name to delete.
     * @param string $tenant The tenant ID to delete the database from.
     */
    public function deleteDatabase(string $database, string $tenant): void
    {
        try {
            $this->httpClient->delete("/api/v2/tenants/$tenant/databases/$database");
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
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
        try {
            $response = $this->httpClient->get("/api/v2/tenants/$tenant/databases/$database/collections", [
                'query' => [
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        return array_map(fn(array $item) => Collection::make($item, $this, $database, $tenant), $result);
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
        try {
            $response = $this->httpClient->post("/api/v2/tenants/$tenant/databases/$database/collections", [
                'json' => $request->toArray()
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        return Collection::make($result, $this, $database, $tenant);
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
        try {
            $response = $this->httpClient->get("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId");
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        return Collection::make($result, $this, $database, $tenant);
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
        try {
            $this->httpClient->put("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId", [
                'json' => $request->toArray(),
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
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
        try {
            $this->httpClient->delete("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId");
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
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
        try {
            $response = $this->httpClient->get("/api/v2/tenants/$tenant/databases/$database/collections_count");
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

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
        try {
            $this->httpClient->post("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/add", [
                'json' => $request->toArray(),
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
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
        try {
            $response = $this->httpClient->get("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/count");
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

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
        try {
            $this->httpClient->post("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/update", [
                'json' => $request->toArray(),
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
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
        try {
            $this->httpClient->post("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/upsert", [
                'json' => $request->toArray(),
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
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
        try {
            $response = $this->httpClient->post("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/get", [
                'json' => $request->toArray(),
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        return GetItemsResponse::from($result);
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
        try {
            $this->httpClient->post("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/delete", [
                'json' => $request->toArray(),
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }
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
        try {
            $response = $this->httpClient->post("/api/v2/tenants/$tenant/databases/$database/collections/$collectionId/query", [
                'json' => $request->toArray(),
            ]);
        } catch (ClientExceptionInterface $e) {
            $this->handleChromaApiException($e);
        }

        $result = json_decode($response->getBody()->getContents(), true);

        return QueryItemsResponse::from($result);
    }


    private function handleChromaApiException(\Exception|ClientExceptionInterface $e): void
    {
        if ($e instanceof ConnectException) {
            $context = $e->getHandlerContext();
            $message = $context['error'] ?? $e->getMessage();
            $code = $context['errno'] ?? $e->getCode();
            throw new ChromaConnectionException($message, $code);
        }

        if ($e instanceof RequestException) {
            $errorString = $e->getResponse()->getBody()->getContents();

            if (preg_match('/(?<={"\"error\"\:\")([^"]*)/', $errorString, $matches)) {
                $errorString = $matches[1];
            }

            $error = json_decode($errorString, true);

            if ($error !== null) {

                // If the structure is 'error' => 'NotFoundError("Collection not found")'
                if (preg_match(
                    '/^(?P<error_type>\w+)\((?P<message>.*)\)$/',
                    $error['error'] ?? '',
                    $matches
                )) {
                    if (isset($matches['message'])) {
                        $error_type = $matches['error_type'] ?? 'UnknownError';
                        $message = $matches['message'];

                        // Remove trailing and leading quotes
                        if (str_starts_with($message, "'") && str_ends_with($message, "'")) {
                            $message = substr($message, 1, -1);
                        }

                        ChromaException::throwSpecific($message, $error_type, $e->getCode());
                    }
                }

                // If the structure is 'detail' => 'Collection not found'
                if (isset($error['detail'])) {
                    $message = $error['detail'];
                    $error_type = ChromaException::inferTypeFromMessage($message);


                    ChromaException::throwSpecific($message, $error_type, $e->getCode());
                }

                // If the structure is {'error': 'Error Type', 'message' : 'Error message'}
                if (isset($error['error']) && isset($error['message'])) {
                    ChromaException::throwSpecific($error['message'], $error['error'], $e->getCode());
                }

                // If the structure is 'error' => 'Collection not found'
                if (isset($error['error'])) {
                    $message = $error['error'];
                    $error_type = ChromaException::inferTypeFromMessage($message);

                    ChromaException::throwSpecific($message, $error_type, $e->getCode());
                }
            }
        }

        throw new ChromaException($e->getMessage(), $e->getCode());
    }
}
