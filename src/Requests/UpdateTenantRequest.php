<?php

namespace Codewithkyrian\ChromaDB\Requests;

class UpdateTenantRequest
{
    /**
     * @param string $name The name of the tenant
     */
    public function __construct(
        public readonly string $name,
    ) {}

    public static function create(array $data): self
    {
        return new self(
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
