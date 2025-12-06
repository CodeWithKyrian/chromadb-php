<?php

declare(strict_types=1);


namespace Codewithkyrian\ChromaDB\Models;

class Tenant
{
    public function __construct(
        /**
         * Name of the tenant.
         *
         * @var string
         */
        public readonly string $name,
    ) {
    }

    public static function fromArray(array $data): self
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
