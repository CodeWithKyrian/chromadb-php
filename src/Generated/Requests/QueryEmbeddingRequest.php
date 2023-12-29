<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Requests;

class QueryEmbeddingRequest
{
    public function __construct(
        /**
         * Optional query condition to filter results based on metadata values.
         *
         * @var array<string, string>
         */
        public readonly ?array $where,

        /**
         * Optional query condition to filter results based on document content.
         *
         * @var array<string, mixed>
         */
        public readonly ?array $whereDocument,

        /**
         * Optional query condition to filter results based on embedding content.
         *
         * @var float[][]
         */
        public readonly ?array $queryEmbeddings,

        /**
         * Optional number of results to return. Defaults to 10.
         */
        public readonly ?int   $nResults,

        /**
         * Optional list of items to include in the response.
         *
         * @var string[]
         */
        public readonly ?array $include,
    )
    {
    }

    public static function create(array $data): self
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