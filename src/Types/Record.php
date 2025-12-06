<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Types;

class Record
{
    /**
     * @param string $id The ID of the item.
     * @param float[]|null $embedding The embedding of the item.
     * @param array<string, mixed>|null $metadata The metadata of the item.
     * @param string|null $document The document content of the item.
     * @param string|null $uri The URI of the item.    
     */
    public function __construct(
        public string $id,
        public ?array $embedding = null,
        public ?array $metadata = null,
        public ?string $document = null,
        public ?string $uri = null,
    ) {
    }

    public static function make(string $id): self
    {
        return new self($id);
    }

    public function withEmbedding(array $embedding): self
    {
        $this->embedding = $embedding;
        return $this;
    }

    public function withMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function withDocument(string $document): self
    {
        $this->document = $document;
        return $this;
    }

    public function withUri(string $uri): self
    {
        $this->uri = $uri;
        return $this;
    }
}
