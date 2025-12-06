<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Requests;

class DeleteItemsRequest
{
    /**
     * @param string[] $ids Optional IDs of the items to delete.
     * @param array<string, string> $where Optional query condition to filter items to delete based on metadata values.
     * @param array<string, string> $whereDocument Optional query condition to filter items to delete based on document content.
     */
    public function __construct(
        public readonly ?array $ids,
        public readonly ?array $where,
        public readonly ?array $whereDocument,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ids: $data['ids'] ?? null,
            where: $data['where'] ?? null,
            whereDocument: $data['where_document'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ids' => $this->ids,
            'where' => $this->where,
            'where_document' => $this->whereDocument,
        ], fn($value) => $value !== null);
    }
}
