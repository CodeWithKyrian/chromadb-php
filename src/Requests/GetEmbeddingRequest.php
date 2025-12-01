<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Requests;

/**
 * Request model for get items from collection.
 */
class GetEmbeddingRequest
{
    /**
     * @param string[] $ids Optional IDs of the items to get.
     * @param array<string, mixed> $where Optional where clause to filter items by.
     * @param array<string, mixed> $whereDocument Optional where clause to filter items by.
     * @param string $sort Optional sort items.
     * @param int $limit Optional limit on the number of items to get.
     * @param int $offset Optional offset on the number of items to get.
     * @param string[] $include Optional list of items to include in the response.
     */
    public function __construct(
        public readonly ?array $ids = null,
        public readonly ?array $where = null,
        public readonly ?array $whereDocument = null,
        public readonly ?string $sort = null,
        public readonly ?int $limit = null,
        public readonly ?int $offset = null,
        public readonly ?array $include = null,
    ) {}

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
        return array_filter([
            'ids' => $this->ids,
            'where' => $this->where,
            'whereDocument' => $this->whereDocument,
            'sort' => $this->sort,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'include' => $this->include,
        ], fn($value) => $value !== null);
    }
}
