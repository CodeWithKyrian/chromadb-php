<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Embeddings;

interface EmbeddingFunction
{
    /**
     * Generates embeddings for the given texts
     *
     * @param string[] $texts
     *
     * @return int[][]
     */
    public function generate(array $texts): array;

}