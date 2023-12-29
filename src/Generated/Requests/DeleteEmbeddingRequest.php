<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Requests;

class DeleteEmbeddingRequest
{
    public function __construct(
        /**
         * Optional IDs of the items to delete.
         *
         * @var string[]
         */
        public readonly ?array $ids,

        /**
         * Optional query condition to filter items to delete based on metadata values.
         *
         * @var array<string, string>
         */
        public readonly ?array $where,

        /**
         * Optional query condition to filter items to delete based on document content.
         *
         * @var array<string, string>
         */
        public readonly ?array $whereDocument,
    )
    {
    }

    public static function create(array $data): self
    {
        return new self(
            ids: $data['ids'] ?? null,
            where: $data['where'] ?? null,
            whereDocument: $data['where_document'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'ids' => $this->ids,
            'where' => $this->where,
            'where_document' => $this->whereDocument,
        ];
    }

}