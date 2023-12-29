<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Requests;

/**
 * Request model for get items from collection.
 */
class GetEmbeddingRequest
{
    public function __construct(
        /**
         * Optional IDs of the items to get.
         *
         * @var string[]
         */
        public readonly ?array $ids = null,

        /**
         * Optional where clause to filter items by.
         *
         * @var array<string, mixed>
         */
        public readonly ?array $where= null,

        /**
         * Optional where clause to filter items by.
         *
         * @var array<string, mixed>
         */
        public readonly ?array $whereDocument= null,

        /**
         * Sort items.
         */
        public readonly ?string $sort= null,

        /**
         * Optional limit on the number of items to get.
         */
        public readonly ?int $limit= null,

        /**
         * Optional offset on the number of items to get.
         */
        public readonly ?int $offset= null,

        /**
         * Optional list of items to include in the response.
         *
         * @var string[]
         */
        public readonly ?array $include= null,
    )
    {
    }

    public static function create(array $data): self
    {
        return new self(
            ids: $data['ids'] ?? null,
            where: $data['where'] ?? null,
            whereDocument: $data['where_document'] ?? null,
            sort: $data['sort'] ?? null,
            limit: $data['limit'] ?? null,
            offset: $data['offset'] ?? null,
            include: $data['include'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'ids' => $this->ids,
            'where' => $this->where,
            'whereDocument' => $this->whereDocument,
            'sort' => $this->sort,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'include' => $this->include,
        ];
    }
}