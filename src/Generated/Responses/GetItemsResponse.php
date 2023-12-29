<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Responses;

/**
 * Response model for getting items from collection.
 */
class GetItemsResponse
{
    public function __construct(
        /**
         * List of ids of the items.
         *
         * @var string[]
         */
        public readonly array $ids,

        /**
         * List of metadata of the items.
         *
         * @var array<string, string>[]
         */
        public readonly ?array $metadatas,

        /**
         * List of embeddings of the items.
         *
         * @var float[][]
         */
        public readonly ?array $embeddings,

        /**
         * List of documents of the items.
         *
         * @var string[]
         */
        public readonly ?array $documents,
    )
    {
    }

    public static function from(array $data): self
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
}