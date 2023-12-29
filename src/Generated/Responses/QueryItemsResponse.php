<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Responses;

/**
 * Response model for querying items from collection.
 */
class QueryItemsResponse
{
    public function __construct(
        /**
         * List of ids of the items.
         *
         * @var string[][]
         */
        public readonly array $ids,


        /**
         * List of embeddings of the items.
         *
         * @var float[][][]
         */
        public readonly ?array $embeddings,

        /**
         * List of metadatas of the items.
         *
         * @var array<string, string>[][]
         */
        public readonly ?array $metadatas,

        /**
         * List of documents of the items.
         *
         * @var string[][]
         */
        public readonly ?array $documents,

        /**
         * List of data of the items.
         *
         * @var string[][]
         */
        public readonly ?array $data,

        /**
         * List of uris of the items.
         *
         * @var string[][]
         */
        public readonly ?array $uris,

        /**
         * List of distances of the items.
         *
         * @var float[][]
         */
        public readonly ?array $distances,
    )
    {
    }

    public static function from(array $data): self
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

}