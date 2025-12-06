<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Models;

use Codewithkyrian\ChromaDB\Api;
use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;
use Codewithkyrian\ChromaDB\Exceptions\InvalidArgumentException;
use Codewithkyrian\ChromaDB\Requests\AddItemsRequest;
use Codewithkyrian\ChromaDB\Requests\DeleteItemsRequest;
use Codewithkyrian\ChromaDB\Requests\GetEmbeddingRequest;
use Codewithkyrian\ChromaDB\Requests\QueryItemsRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateCollectionRequest;
use Codewithkyrian\ChromaDB\Requests\UpdateItemsRequest;
use Codewithkyrian\ChromaDB\Responses\GetItemsResponse;
use Codewithkyrian\ChromaDB\Responses\QueryItemsResponse;
use Codewithkyrian\ChromaDB\Types\Includes;
use Codewithkyrian\ChromaDB\Types\Record;

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
        public readonly Api $api,
        public readonly string $name,
        public readonly string $id,
        public readonly ?array $metadata = null,
        public readonly ?string $database = null,
        public readonly ?string $tenant = null,
        public ?EmbeddingFunction $embeddingFunction = null,
    ) {}

    public static function fromArray(array $data, Api $api, string $database, string $tenant): self
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
     * @param string[]|Record[] $ids The IDs of the items to add, or an array of Record objects.
     * @param number[][]|null $embeddings The embeddings of the items to add (optional).
     * @param array<string, array<string, mixed>>|null $metadatas The metadatas of the items to add (optional).
     * @param string[]|null $documents The documents of the items to add (optional).
     * @return void
     */
    public function add(
        array $ids,
        ?array $embeddings = null,
        ?array $metadatas = null,
        ?array $documents = null,
    ): void {
        if (!empty($ids) && $ids[0] instanceof Record) {
            $records = $ids;
            $ids = [];
            $embeddings = [];
            $metadatas = [];
            $documents = [];

            foreach ($records as $record) {
                $ids[] = $record->id;
                $embeddings[] = $record->embedding;
                $metadatas[] = $record->metadata;
                $documents[] = $record->document;
            }
        }

        $validated = $this->validate(
            ids: $ids,
            embeddings: $embeddings,
            metadatas: $metadatas,
            documents: $documents,
            requireEmbeddingsOrDocuments: true,
        );

        $request = new AddItemsRequest(
            embeddings: $validated['embeddings'],
            metadatas: $validated['metadatas'],
            ids: $validated['ids'],
            documents: $validated['documents'],
        );

        $this->api->addCollectionItems($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Update the embeddings, documents, and/or metadatas of existing items.
     *
     * @param string[]|Record[] $ids The IDs of the items to update, or an array of Record objects.
     * @param number[][]|null $embeddings The embeddings of the items to update (optional).
     * @param array<string, array<string, mixed>>|null $metadatas The metadatas of the items to update (optional).
     * @param string[]|null $documents The documents of the items to update (optional).
     *
     */
    public function update(
        array $ids,
        ?array $embeddings = null,
        ?array $metadatas = null,
        ?array $documents = null,
    ) {
        if (!empty($ids) && $ids[0] instanceof Record) {
            $records = $ids;
            $ids = [];
            $embeddings = [];
            $metadatas = [];
            $documents = [];

            foreach ($records as $record) {
                $ids[] = $record->id;
                $embeddings[] = $record->embedding;
                $metadatas[] = $record->metadata;
                $documents[] = $record->document;
            }
        }

        $validated = $this->validate(
            ids: $ids,
            embeddings: $embeddings,
            metadatas: $metadatas,
            documents: $documents,
            requireEmbeddingsOrDocuments: false,
        );

        $request = new UpdateItemsRequest(
            embeddings: $validated['embeddings'],
            ids: $validated['ids'],
            metadatas: $validated['metadatas'],
            documents: $validated['documents'],
        );

        $this->api->updateCollectionItems($this->id, $this->database, $this->tenant, $request);
    }

    /**
     * Upsert items in the collection.
     *
     * @param string[]|Record[] $ids The IDs of the items to upsert, or an array of Record objects.
     * @param number[][]|null $embeddings The embeddings of the items to upsert (optional).
     * @param array<string, array<string, mixed>>|null $metadatas The metadatas of the items to upsert (optional).
     * @param string[]|null $documents The documents of the items to upsert (optional).
     *
     */
    public function upsert(
        array $ids,
        ?array $embeddings = null,
        ?array $metadatas = null,
        ?array $documents = null,
    ): void {
        if (!empty($ids) && $ids[0] instanceof Record) {
            $records = $ids;
            $ids = [];
            $embeddings = [];
            $metadatas = [];
            $documents = [];

            foreach ($records as $record) {
                $ids[] = $record->id;
                $embeddings[] = $record->embedding;
                $metadatas[] = $record->metadata;
                $documents[] = $record->document;
            }
        }

        $validated = $this->validate(
            ids: $ids,
            embeddings: $embeddings,
            metadatas: $metadatas,
            documents: $documents,
            requireEmbeddingsOrDocuments: true,
        );

        $request = new AddItemsRequest(
            embeddings: $validated['embeddings'],
            metadatas: $validated['metadatas'],
            ids: $validated['ids'],
            documents: $validated['documents'],
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
     * @param array|null $ids The IDs of the items to get (optional).
     * @param array|null $where The where clause to filter items by (optional).
     * @param array|null $whereDocument The where clause to filter items by (optional).
     * @param int|null $limit The limit on the number of items to get (optional).
     * @param int|null $offset The offset on the number of items to get (optional).
     * @param string[]|Includes[]|null $include The list of fields to include in the response (optional).
     */
    public function get(
        ?array $ids = null,
        ?array $where = null,
        ?array $whereDocument = null,
        ?int $limit = null,
        ?int $offset = null,
        ?array $include = null
    ): GetItemsResponse {
        $include ??= ['embeddings', 'metadatas', 'distances'];

        $include = array_map(fn($i) => $i instanceof Includes ? $i->value : $i, $include);

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
     * @param string[]|Includes[]|null $include The list of fields to include in the response (optional).
     */
    public function peek(int $limit = 10, ?array $include = null): GetItemsResponse
    {
        $include ??= ['embeddings', 'metadatas', 'distances'];

        $include = array_map(fn($i) => $i instanceof Includes ? $i->value : $i, $include);

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
     * @param int $nResults The number of results to return (optional).
     * @param ?array $where The where clause to filter items to search based on metadata values (optional).
     * @param ?array $whereDocument The where clause to filter to search based on document content (optional).
     * @param string[]|Includes[]|null $include The list of fields to include in the response (optional).
     */
    public function query(
        ?array $queryEmbeddings = null,
        ?array $queryTexts = null,
        int $nResults = 10,
        ?array $where = null,
        ?array $whereDocument = null,
        ?array $include = null
    ): QueryItemsResponse {
        $include ??= ['embeddings', 'metadatas', 'distances'];

        $include = array_map(fn($i) => $i instanceof Includes ? $i->value : $i, $include);

        if ($nResults <= 0) {
            throw new InvalidArgumentException('Expected nResults to be a positive integer');
        }

        if (
            !(($queryEmbeddings != null xor $queryTexts != null))
        ) {
            throw new InvalidArgumentException(
                'You must provide only one of queryEmbeddings or queryTexts'
            );
        }

        $finalEmbeddings = [];

        if ($queryEmbeddings == null) {
            if ($this->embeddingFunction == null) {
                throw new InvalidArgumentException(
                    'You must provide an embedding function if you did not provide embeddings'
                );
            } elseif ($queryTexts != null) {
                $finalEmbeddings = $this->embeddingFunction->generate($queryTexts);
            } else {
                throw new InvalidArgumentException(
                    'If you did not provide queryEmbeddings, you must provide queryTexts'
                );
            }
        } else {
            foreach ($queryEmbeddings as $i => $embedding) {
                if (!is_array($embedding)) {
                    throw new InvalidArgumentException(sprintf(
                        "Expected query embedding at index %d to be an array, got %s",
                        $i,
                        gettype($embedding)
                    ));
                }

                foreach ($embedding as $j => $value) {
                    if (!is_float($value)) {
                        throw new InvalidArgumentException(sprintf(
                            "Expected query embedding value at index %d.%d to be a float, got %s",
                            $i,
                            $j,
                            gettype($value)
                        ));
                    }
                }
            }
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
     * @return array{ids: string[], embeddings: int[][], metadatas: array[], documents: string[]}
     */
    protected function validate(
        array $ids,
        ?array $embeddings,
        ?array $metadatas,
        ?array $documents,
        bool $requireEmbeddingsOrDocuments
    ): array {

        if ($requireEmbeddingsOrDocuments) {
            if ($embeddings === null && $documents === null) {
                throw new InvalidArgumentException(
                    'You must provide embeddings or documents'
                );
            }
        }

        if (
            $embeddings != null && count($embeddings) != count($ids)
            || $metadatas != null && count($metadatas) != count($ids)
            || $documents != null && count($documents) != count($ids)
        ) {
            throw new InvalidArgumentException(
                'The number of ids, embeddings, metadatas, and documents must be the same'
            );
        }

        // Validate metadatas
        if ($metadatas !== null) {
            foreach ($metadatas as $i => $metadata) {
                if ($metadata !== null && !is_array($metadata)) {
                    throw new InvalidArgumentException(sprintf(
                        "Expected metadata at index %d to be an array, got %s",
                        $i,
                        gettype($metadata)
                    ));
                }
            }
        }

        // Validate embeddings
        if ($embeddings == null) {
            if ($this->embeddingFunction == null) {
                throw new InvalidArgumentException(
                    'You must provide an embedding function if you did not provide embeddings'
                );
            } elseif ($documents != null) {
                $finalEmbeddings = $this->embeddingFunction->generate($documents);
            } else {
                throw new InvalidArgumentException(
                    'If you did not provide embeddings, you must provide documents'
                );
            }
        } else {
            foreach ($embeddings as $i => $embedding) {
                if (!is_array($embedding)) {
                    throw new InvalidArgumentException(sprintf(
                        "Expected embedding at index %d to be an array, got %s",
                        $i,
                        gettype($embedding)
                    ));
                }

                foreach ($embedding as $j => $value) {
                    if (!is_float($value)) {
                        throw new InvalidArgumentException(sprintf(
                            "Expected embedding value at index %d.%d to be a float, got %s",
                            $i,
                            $j,
                            gettype($value)
                        ));
                    }
                }
            }

            $finalEmbeddings = $embeddings;
        }

        $ids = array_map(function ($id) {
            if (is_object($id) && method_exists($id, '__toString')) {
                $id = (string) $id;
            }
            if (!is_string($id)) {
                throw new InvalidArgumentException('Expected IDs to be strings, got ' . gettype($id));
            }
            if ($id === '') {
                throw new InvalidArgumentException('Expected IDs to be an array of non-empty strings');
            }
            return $id;
        }, $ids);

        $uniqueIds = array_unique($ids);
        if (count($uniqueIds) !== count($ids)) {
            $duplicateIds = array_filter($ids, function ($id) use ($ids) {
                return count(array_keys($ids, $id)) > 1;
            });
            throw new InvalidArgumentException('Expected IDs to be unique, found duplicates for: ' . implode(', ', array_unique($duplicateIds)));
        }

        return [
            'ids' => $ids,
            'embeddings' => $finalEmbeddings,
            'metadatas' => $metadatas,
            'documents' => $documents,
        ];
    }
}
