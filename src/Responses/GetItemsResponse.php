<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Responses;

use Codewithkyrian\ChromaDB\Types\Record;

/**
 * Response model for getting items from collection.
 */
class GetItemsResponse
{
    /**
     * @param string[] $ids List of ids of the items.
     * @param array<string, string>[]|null $metadatas List of metadata of the items.
     * @param float[][]|null $embeddings List of embeddings of the items.
     * @param string[]|null $documents List of documents of the items.
     */
    public function __construct(
        public readonly array $ids,
        public readonly ?array $metadatas,
        public readonly ?array $embeddings,
        public readonly ?array $documents,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ids: $data['ids'],
            metadatas: $data['metadatas'] ?? null,
            embeddings: $data['embeddings'] ?? null,
            documents: $data['documents'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ids' => $this->ids,
            'metadatas' => $this->metadatas,
            'embeddings' => $this->embeddings,
            'documents' => $this->documents,
        ]);
    }

    /**
     * @return Record[]
     */
    public function asRecords(): array
    {
        $records = [];
        foreach ($this->ids as $index => $id) {
            $records[] = new Record(
                id: $id,
                embedding: $this->embeddings[$index] ?? null,
                metadata: $this->metadatas[$index] ?? null,
                document: $this->documents[$index] ?? null,
            );
        }
        return $records;
    }
}
