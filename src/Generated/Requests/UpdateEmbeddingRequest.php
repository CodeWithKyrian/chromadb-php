<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Requests;

class UpdateEmbeddingRequest
{
    public function __construct(
        /**
         * Optional embeddings of the items to update.
         *
         * @var float[][]
         */
        public readonly ?array $embeddings,


        /**
         * IDs of the items to update.
         *
         * @var string[]
         */
        public readonly array  $ids,

        /**
         * Optional metadatas of the items to update.
         *
         * @var array<string, string>[]
         */
        public readonly ?array $metadatas,

        /**
         * Optional documents of the items to update.
         *
         * @var string[]
         */
        public readonly ?array $documents,

        /**
         * Optional uris of the items to update.
         *
         * @var string[]
         */
        public readonly ?array $images,
    )
    {
    }

    public static function create(array $data): self
    {
        return new self(
            embeddings: $data['embeddings'] ?? null,
            ids: $data['ids'],
            metadatas: $data['metadatas'] ?? null,
            documents: $data['documents'] ?? null,
            images: $data['images'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'embeddings' => $this->embeddings,
            'ids' => $this->ids,
            'metadatas' => $this->metadatas,
            'documents' => $this->documents,
        ]);
    }
}