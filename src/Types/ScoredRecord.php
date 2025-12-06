<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Types;

class ScoredRecord extends Record
{
    /**
     * @param string $id The ID of the item.
     * @param float[]|null $embedding The embedding of the item.
     * @param array<string, mixed>|null $metadata The metadata of the item.
     * @param string|null $document The document content of the item.
     * @param string|null $uri The URI of the item.
     * @param float|null $distance The distance of the item
     */
    public function __construct(
        string $id,
        ?array $embedding = null,
        ?array $metadata = null,
        ?string $document = null,
        ?string $uri = null,
        public ?float $distance = null,
    ) {
        parent::__construct($id, $embedding, $metadata, $document, $uri);
    }

    public function withDistance(float $distance): self
    {
        $this->distance = $distance;
        return $this;
    }
}
