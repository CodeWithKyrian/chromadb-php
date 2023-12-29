<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Embeddings;

use Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction;

interface OpenAIAPIInterface {
    /**
     * Creates a new embedding function
     * @param string $model
     * @param string[] $input
     * @param string $user
     *
     * @return int[][]
     */
    public function createEmbedding(string $model, array $input, string $user ): array;
}

class OpenAIEmbeddingFunction implements EmbeddingFunction
{

    public function __construct(
        public readonly string $apiKey,
        public readonly string $organizationId,
        public readonly string $model,

    )
    {
    }

    /**
     * @inheritDoc
     */
    public function generate(array $texts): array
    {
        return [[1,2,3,4,5,6,7,8,9,10], [1,2,3,4,5,6,7,8,9,10]];
    }
}