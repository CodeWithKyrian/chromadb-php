<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB;

class ChromaDB
{
    /**
     * Creates a new factory instance to configure a custom ChromaDB Client
     */
    public static function factory(): Factory
    {
        return new Factory();
    }

    /**
     * @deprecated Use ChromaDB::local()->connect() or ChromaDB::factory()->connect() instead.
     */
    public static function client(): Client
    {
        return self::factory()->connect();
    }

    /**
     * Creates a new factory instance configured for a local/self-hosted ChromaDB instance.
     */
    public static function local(
        string $host = 'http://localhost',
        ?int $port = 8000,
        ?string $tenant = null,
        ?string $database = null
    ): Factory {
        $factory = self::factory()
            ->withHost($host)
            ->withPort($port);

        if ($tenant) {
            $factory->withTenant($tenant);
        }

        if ($database) {
            $factory->withDatabase($database);
        }

        return $factory;
    }

    /**
     * Creates a new factory instance configured for Chroma Cloud.
     */
    public static function cloud(string $apiKey, ?string $tenant = null, ?string $database = null): Factory
    {
        $factory = self::factory()
            ->withHost('https://api.trychroma.com')
            ->withPort(null)
            ->withHeader('X-Chroma-Token', $apiKey);

        if ($tenant) {
            $factory->withTenant($tenant);
        }

        if ($database) {
            $factory->withDatabase($database);
        }

        return $factory;
    }
}
