<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Responses;

use Codewithkyrian\ChromaDB\Types\ScoredRecord;

/**
 * Response model for querying items from collection.
 */
class QueryItemsResponse
{
    public function __construct(
        /**
         * @param string[][] $ids List of ids of the items.
         * @param float[][][]|null $embeddings List of embeddings of the items.
         * @param array<string, string>[][]|null $metadatas List of metadatas of the items.
         * @param string[][]|null $documents List of documents of the items.
         * @param string[][]|null $data List of data of the items.
         * @param string[][]|null $uris List of uris of the items.
         * @param float[][]|null $distances List of distances of the items.
         */
        public readonly array $ids,
        public readonly ?array $embeddings,
        public readonly ?array $metadatas,
        public readonly ?array $documents,
        public readonly ?array $data,
        public readonly ?array $uris,
        public readonly ?array $distances,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ids: $data['ids'],
            embeddings: $data['embeddings'] ?? null,
            metadatas: $data['metadatas'] ?? null,
            documents: $data['documents'] ?? null,
            data: $data['data'] ?? null,
            uris: $data['uris'] ?? null,
            distances: $data['distances'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ids' => $this->ids,
            'embeddings' => $this->embeddings,
            'metadatas' => $this->metadatas,
            'documents' => $this->documents,
            'data' => $this->data,
            'uris' => $this->uris,
            'distances' => $this->distances,
        ]);
    }

    /**
     * @return ScoredRecord[][]
     */
    public function asRecords(): array
    {
        $records = [];
        foreach ($this->ids as $queryIndex => $ids) {
            $queryRecords = [];
            foreach ($ids as $resultIndex => $id) {
                $queryRecords[] = new ScoredRecord(
                    id: $id,
                    embedding: $this->embeddings[$queryIndex][$resultIndex] ?? null,
                    metadata: $this->metadatas[$queryIndex][$resultIndex] ?? null,
                    document: $this->documents[$queryIndex][$resultIndex] ?? null,
                    distance: $this->distances[$queryIndex][$resultIndex] ?? null,
                );
            }
            $records[] = $queryRecords;
        }
        return $records;
    }
}
