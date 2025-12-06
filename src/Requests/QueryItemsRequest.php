<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Requests;

class QueryItemsRequest
{
    /**
     * @param array<string, string> $where Optional query condition to filter results based on metadata values.
     * @param array<string, mixed> $whereDocument Optional query condition to filter results based on document content.
     * @param float[][] $queryEmbeddings Optional query condition to filter results based on embedding content.
     * @param int $nResults Optional number of results to return. Defaults to 10.
     * @param string[] $include Optional list of items to include in the response.
     */
    public function __construct(
        public readonly ?array $where,
        public readonly ?array $whereDocument,
        public readonly ?array $queryEmbeddings,
        public readonly ?int   $nResults,
        public readonly ?array $include,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            where: $data['where'] ?? null,
            whereDocument: $data['where_document'] ?? null,
            queryEmbeddings: $data['query_embeddings'] ?? null,
            nResults: $data['n_results'] ?? null,
            include: $data['include'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'where' => $this->where,
            'where_document' => $this->whereDocument,
            'query_embeddings' => $this->queryEmbeddings,
            'n_results' => $this->nResults,
            'include' => $this->include,
        ], fn($value) => $value !== null);
    }
}
