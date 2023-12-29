<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Generated\Models;

class Database
{
    public function __construct(
        /**
         * Id of the database.
         */
        public readonly string $id,

        /**
         * Name of the database.
         */
        public readonly string $name,

        /**
         * Tenant of the database.
         */
        public readonly ?string $tenant,
    )
    {
    }

    public static function make(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            tenant: $data['tenant'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tenant' => $this->tenant,
        ];
    }

}