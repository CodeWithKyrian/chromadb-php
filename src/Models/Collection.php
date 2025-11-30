<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Models;

use Codewithkyrian\ChromaDB\Api;
use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Codewithkyrian\ChromaDB\Requests\AddItemsRequest;
use Codewithkyrian\ChromaDB\Requests\DeleteItemsRequest;
use Codewithkyrian\ChromaDB\Requests\GetEmbeddingRequest;
use Codewithkyrian\ChromaDB\Requests\QueryItemsRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateCollectionRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateItemsRequest;
use Codewithkyrian\ChromaDB\Responses\GetItemsResponse;
use Codewithkyrian\ChromaDB\Responses\QueryItemsResponse;

class Collection
{
    /**
     * @param Api $api The API client instance.
     * @param string $name Name of the collection.
     * @param string $id Unique identifier for the collection.
     * @param array|null $metadata Collection-level metadata.
     * @param string|null $database Database name.
     * @param string|null $tenant Tenant name.
     * @param EmbeddingFunction|null $embeddingFunction Optional embedding function. Must match the one used to create the collection.
     */
    public function __construct(
        public readonly Api       $api,
        public readonly string    $name,
        public readonly string    $id,
        public readonly ?array    $metadata = null,
        public readonly ?string   $database = null,
        public readonly ?string   $tenant = null,
        public ?EmbeddingFunction $embeddingFunction = null,
    ) {}

    public static function make(array $data, Api $api, string $database, string $tenant): self
    {
        return new self(
            api: $api,
            name: $data['name'],
            id: $data['id'],
            metadata: $data['metadata'] ?? null,
            database: $database,
            tenant: $tenant
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'id' => $this->id,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Add items to the collection.
     *
     * @param string[] $ids The IDs of the items to add.
     * @param number[][]|null $embeddings The embeddings of the items to add (optional).
     * @param array<string, array<string, mixed>>|null $metadatas The metadatas of the items to add (optional).
     * @param string[]|null $documents The documents of the items to add (optional).
     * @param string[]|null $images The base64 encoded images of the items to add (optional).
     * @return void
     */
    public function add(
        array  $ids,
        ?array $embeddings = null,
        ?array $metadatas = null,
        ?array $documents = null,
        ?array $images = null
    ): void {
        $validated = $this->validate(
            ids: $ids,
            embeddings: $embeddings,
            metadatas: $metadatas,
            documents: $documents,
            images: $images,
            requireEmbeddingsOrDocuments: true,
        );

        $request = new AddItemsRequest(
            embeddings: $validated['embeddings'],
            metadatas: $validated['metadatas'],
            ids: $validated['ids'],
            documents: $validated['documents'],
            images: $validated['images'],
        );

        $this->api->addCollectionItems($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Update the embeddings, documents, and/or metadatas of existing items.
     *
     * @param string[] $ids The IDs of the items to update.
     * @param number[][]|null $embeddings The embeddings of the items to update (optional).
     * @param array<string, array<string, mixed>>|null $metadatas The metadatas of the items to update (optional).
     * @param string[]|null $documents The documents of the items to update (optional).
     * @param string[]|null $images The base64 encoded images of the items to update (optional).
     *
     */
    public function update(
        array  $ids,
        ?array $embeddings = null,
        ?array $metadatas = null,
        ?array $documents = null,
        ?array $images = null
    ) {
        $validated = $this->validate(
            ids: $ids,
            embeddings: $embeddings,
            metadatas: $metadatas,
            documents: $documents,
            images: $images,
            requireEmbeddingsOrDocuments: false,
        );

        $request = new UpdateItemsRequest(
            embeddings: $validated['embeddings'],
            ids: $validated['ids'],
            metadatas: $validated['metadatas'],
            documents: $validated['documents'],
            images: $validated['images'],
        );

        $this->api->updateCollectionItems($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Upsert items in the collection.
     *
     * @param string[] $ids The IDs of the items to upsert.
     * @param number[][]|null $embeddings The embeddings of the items to upsert (optional).
     * @param array<string, array<string, mixed>>|null $metadatas The metadatas of the items to upsert (optional).
     * @param string[]|null $documents The documents of the items to upsert (optional).
     * @param string[]|null $images The base64 encoded images of the items to upsert (optional).
     *
     */
    public function upsert(
        array  $ids,
        ?array $embeddings = null,
        ?array $metadatas = null,
        ?array $documents = null,
        ?array $images = null
    ): void {
        $validated = $this->validate(
            ids: $ids,
            embeddings: $embeddings,
            metadatas: $metadatas,
            documents: $documents,
            images: $images,
            requireEmbeddingsOrDocuments: true,
        );

        $request = new AddItemsRequest(
            embeddings: $validated['embeddings'],
            metadatas: $validated['metadatas'],
            ids: $validated['ids'],
            documents: $validated['documents'],
            images: $validated['images'],
        );

        $this->api->upsertCollectionItems($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Count the number of items in the collection.
     */
    public function count(): int
    {
        return $this->api->countCollectionItems($this->id, $this->database, $this->tenant);
    }

    /**
     * Get items from the collection.
     *
     * @param array $ids The IDs of the items to get (optional).
     * @param array $where The where clause to filter items by (optional).
     * @param array $whereDocument The where clause to filter items by (optional).
     * @param int $limit The limit on the number of items to get (optional).
     * @param int $offset The offset on the number of items to get (optional).
     * @param string[] $include The list of fields to include in the response (optional).
     */
    public function get(
        ?array $ids = null,
        ?array $where = null,
        ?array $whereDocument = null,
        ?int   $limit = null,
        ?int   $offset = null,
        ?array $include = null
    ): GetItemsResponse {
        $include ??= ['embeddings', 'metadatas', 'distances'];

        $request = new GetEmbeddingRequest(
            ids: $ids,
            where: $where,
            whereDocument: $whereDocument,
            limit: $limit,
            offset: $offset,
            include: $include,
        );

        return $this->api->getCollectionItems($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Retrieves a preview of records from the collection.
     *
     * @param int $limit The number of entries to return. Defaults to 10.
     * @param string[] $include The list of fields to include in the response (optional).
    */
    public function peek(int $limit = 10, ?array $include = null): GetItemsResponse {
        $include ??= ['embeddings', 'metadatas', 'distances'];
        
        $request = new GetEmbeddingRequest(
            limit: $limit,
            include: $include,
        );
        
        return $this->api->getCollectionItems($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Deletes items from the collection.
     *
     * @param ?array $ids The IDs of the items to delete.
     * @param ?array $where The where clause to filter items to delete based on metadata values (optional).
     * @param ?array $whereDocument The where clause to filter to delete based on document content (optional).
     */
    public function delete(?array $ids = null, ?array $where = null, ?array $whereDocument = null): void
    {
        $request = new DeleteItemsRequest(
            ids: $ids,
            where: $where,
            whereDocument: $whereDocument,
        );

        $this->api->deleteCollectionItems($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Performs similarity search on the collection.
     * 
     * @param number[][]|null $queryEmbeddings The embeddings of the query (optional).
     * @param string[]|null $queryTexts The texts of the query (optional).
     * @param string[]|null $queryImages The images of the query (optional).
     * @param int $nResults The number of results to return (optional).
     * @param ?array $where The where clause to filter items to search based on metadata values (optional).
     * @param ?array $whereDocument The where clause to filter to search based on document content (optional).
     * @param ?array $include The list of fields to include in the response (optional).
     */
    public function query(
        ?array $queryEmbeddings = null,
        ?array $queryTexts = null,
        ?array $queryImages = null,
        int    $nResults = 10,
        ?array $where = null,
        ?array $whereDocument = null,
        ?array $include = null
    ): QueryItemsResponse {
        $include ??= ['embeddings', 'metadatas', 'distances'];

        if (
            !(($queryEmbeddings != null xor $queryTexts != null xor $queryImages != null))
        ) {
            throw new \InvalidArgumentException(
                'You must provide only one of queryEmbeddings, queryTexts, queryImages, or queryUris'
            );
        }

        $finalEmbeddings = [];

        if ($queryEmbeddings == null) {
            if ($this->embeddingFunction == null) {
                throw new \InvalidArgumentException(
                    'You must provide an embedding function if you did not provide embeddings'
                );
            } elseif ($queryTexts != null) {
                $finalEmbeddings = $this->embeddingFunction->generate($queryTexts);
            } elseif ($queryImages != null) {
                $finalEmbeddings = $this->embeddingFunction->generate($queryImages);
            } else {
                throw new \InvalidArgumentException(
                    'If you did not provide embeddings, you must provide documents or images'
                );
            }
        } else {
            $finalEmbeddings = $queryEmbeddings;
        }


        $request = new QueryItemsRequest(
            where: $where,
            whereDocument: $whereDocument,
            queryEmbeddings: $finalEmbeddings,
            nResults: $nResults,
            include: $include,
        );

        return $this->api->queryCollectionItems($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Modify the collection name or metadata.
     */
    public function modify(string $name, array $metadata): void
    {
        $request = new UpdateCollectionRequest($name, $metadata);

        $this->api->updateCollection($this->id, $this->database, $this->tenant, $request);
    }

    public function setEmbeddingFunction(EmbeddingFunction $embeddingFunction): void
    {
        $this->embeddingFunction = $embeddingFunction;
    }

    /**
     * Validates the inputs to the add, upsert, and update methods.
     *
     * @return array{ids: string[], embeddings: int[][], metadatas: array[], documents: string[], images: string[], uris: string[]}
     */
    protected
    function validate(
        array  $ids,
        ?array $embeddings,
        ?array $metadatas,
        ?array $documents,
        ?array $images,
        bool   $requireEmbeddingsOrDocuments
    ): array {

        if ($requireEmbeddingsOrDocuments) {
            if ($embeddings === null && $documents === null && $images === null) {
                throw new \InvalidArgumentException(
                    'You must provide embeddings, documents, or images'
                );
            }
        }

        if (
            $embeddings != null && count($embeddings) != count($ids)
            || $metadatas != null && count($metadatas) != count($ids)
            || $documents != null && count($documents) != count($ids)
            || $images != null && count($images) != count($ids)
        ) {
            throw new \InvalidArgumentException(
                'The number of ids, embeddings, metadatas, documents, and images  must be the same'
            );
        }

        if ($embeddings == null) {
            if ($this->embeddingFunction == null) {
                throw new \InvalidArgumentException(
                    'You must provide an embedding function if you did not provide embeddings'
                );
            } elseif ($documents != null) {
                $finalEmbeddings = $this->embeddingFunction->generate($documents);
            } elseif ($images != null) {
                $finalEmbeddings = $this->embeddingFunction->generate($images);
            } else {
                throw new \InvalidArgumentException(
                    'If you did not provide embeddings, you must provide documents or images'
                );
            }
        } else {
            $finalEmbeddings = $embeddings;
        }

        $ids = array_map(function ($id) {
            $id = (string)$id;
            if ($id === '') {
                throw new \InvalidArgumentException('Expected IDs to be non-empty strings');
            }
            return $id;
        }, $ids);

        $uniqueIds = array_unique($ids);
        if (count($uniqueIds) !== count($ids)) {
            $duplicateIds = array_filter($ids, function ($id) use ($ids) {
                return count(array_keys($ids, $id)) > 1;
            });
            throw new \InvalidArgumentException('Expected IDs to be unique, found duplicates for: ' . implode(', ', $duplicateIds));
        }


        return [
            'ids' => $ids,
            'embeddings' => $finalEmbeddings,
            'metadatas' => $metadatas,
            'documents' => $documents,
            'images' => $images,
        ];
    }
}
