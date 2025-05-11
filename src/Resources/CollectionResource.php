<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Resources;

use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Codewithkyrian\ChromaDB\Generated\ChromaApiClient;
use Codewithkyrian\ChromaDB\Generated\Models\Collection;
use Codewithkyrian\ChromaDB\Generated\Requests\AddEmbeddingRequest;
use Codewithkyrian\ChromaDB\Generated\Requests\DeleteEmbeddingRequest;
use Codewithkyrian\ChromaDB\Generated\Requests\GetEmbeddingRequest;
use Codewithkyrian\ChromaDB\Generated\Requests\QueryEmbeddingRequest;
use Codewithkyrian\ChromaDB\Generated\Requests\UpdateCollectionRequest;
use Codewithkyrian\ChromaDB\Generated\Requests\UpdateEmbeddingRequest;
use Codewithkyrian\ChromaDB\Generated\Responses\GetItemsResponse;
use Codewithkyrian\ChromaDB\Generated\Responses\QueryItemsResponse;

class CollectionResource
{
    public function __construct(
        /**
         * The name of the collection.
         */
        public readonly string             $name,

        /**
         * The ID of the collection.
         */
        public readonly string             $id,

        /**
         * The metadata of the collection.
         */
        public readonly ?array             $metadata,

        /**
         * The database name.
         */
        public readonly string             $database,

        /**
         * The tenant name.
         */
        public readonly string             $tenant,

        /**
         * The embedding function of the collection.
         */
        public readonly ?EmbeddingFunction $embeddingFunction,

        /**
         * The Chroma API client.
         */
        public readonly ChromaApiClient    $apiClient,

    ) {}

    public static function make(Collection $collection, string $database, string $tenant, ?EmbeddingFunction $embeddingFunction, ChromaApiClient $apiClient): self
    {
        return new self(
            name: $collection->name,
            id: $collection->id,
            metadata: $collection->metadata,
            database: $database,
            tenant: $tenant,
            embeddingFunction: $embeddingFunction,
            apiClient: $apiClient,
        );
    }

    /**
     * Add items to the collection.
     *
     * @param array $ids The IDs of the items to add.
     * @param ?array $embeddings The embeddings of the items to add (optional).
     * @param ?array $metadatas The metadatas of the items to add (optional).
     * @param ?array $documents The documents of the items to add (optional).
     * @param ?array $images The base64 encoded images of the items to add (optional).
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


        $request = new AddEmbeddingRequest(
            embeddings: $validated['embeddings'],
            metadatas: $validated['metadatas'],
            ids: $validated['ids'],
            documents: $validated['documents'],
            images: $validated['images'],
        );


        $this->apiClient->add($this->id, $this->database, $this->tenant, $request);
    }


    /**
     * Update the embeddings, documents, and/or metadatas of existing items.
     *
     * @param array $ids The IDs of the items to update.
     * @param ?array $embeddings The embeddings of the items to update (optional).
     * @param ?array $metadatas The metadatas of the items to update (optional).
     * @param ?array $documents The documents of the items to update (optional).
     * @param ?array $images The base64 encoded images of the items to update (optional).
     *
     */
    public function update(
        array $ids,
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

        $request = new UpdateEmbeddingRequest(
            embeddings: $validated['embeddings'],
            ids: $validated['ids'],
            metadatas: $validated['metadatas'],
            documents: $validated['documents'],
            images: $validated['images'],
        );

        $this->apiClient->update($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Upsert items in the collection.
     *
     * @param array $ids The IDs of the items to upsert.
     * @param ?array $embeddings The embeddings of the items to upsert (optional).
     * @param ?array $metadatas The metadatas of the items to upsert (optional).
     * @param ?array $documents The documents of the items to upsert (optional).
     * @param ?array $images The base64 encoded images of the items to upsert (optional).
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

        $request = new AddEmbeddingRequest(
            embeddings: $validated['embeddings'],
            metadatas: $validated['metadatas'],
            ids: $validated['ids'],
            documents: $validated['documents'],
            images: $validated['images'],
        );

        $this->apiClient->upsert($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Count the number of items in the collection.
     */
    public function count(): int
    {
        return $this->apiClient->count($this->id, $this->database, $this->tenant);
    }

    /**
     * Returns the first `$limit` entries of the collection.
     *
     * @param int $limit The number of entries to return. Defaults to 10.
     * @param string[] $include The list of fields to include in the response (optional).
     */
    public function peek(
        int $limit = 10,
        ?array $include = null
    ): GetItemsResponse {
        $include ??= ['embeddings', 'metadatas', 'distances'];

        $request = new GetEmbeddingRequest(
            limit: $limit,
            include: $include,
        );

        return $this->apiClient->get($this->id, $this->database, $this->tenant, $request);
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

        return $this->apiClient->get($this->id, $this->database, $this->tenant, $request);
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
        $request = new DeleteEmbeddingRequest(
            ids: $ids,
            where: $where,
            whereDocument: $whereDocument,
        );

        $this->apiClient->delete($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Performs a query on the collection using the specified parameters.
     *
     *
     */
    public function query(
        ?array $queryEmbeddings = null,
        ?array $queryTexts = null,
        ?array $queryImages = null,
        int   $nResults = 10,
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


        $request = new QueryEmbeddingRequest(
            where: $where,
            whereDocument: $whereDocument,
            queryEmbeddings: $finalEmbeddings,
            nResults: $nResults,
            include: $include,
        );

        return $this->apiClient->getNearestNeighbors($this->id, $this->database, $this->tenant, $request);
    }


    /**
     * Modify the collection name or metadata.
     */
    public function modify(string $name, array $metadata): void
    {
        $request = new UpdateCollectionRequest($name, $metadata);

        $this->apiClient->updateCollection($this->id, $this->database, $this->tenant, $request);
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
