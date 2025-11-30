<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB;

class ChromaDB
{
    public static function client(): Client
    {
        return self::factory()->connect();
    }

    /**
     * Creates a new factory instance to configure a custom ChromaDB Client
     */
    public static function factory(): Factory
    {
        return new Factory();
    }

    /**
     * Creates a new factory instance configured for Chroma Cloud.
     */
    public static function cloud(string $apiKey, ?string $tenant = null, ?string $database = null): Factory
    {
        $factory = self::factory()
            ->withHost('https://api.trychroma.com')
            ->withPort(8000)
            ->withHeader('X-Chroma-Token', $apiKey);

        if ($tenant) {
            $factory->withTenant($tenant);
        }

        if ($database) {
            $factory->withDatabase($database);
        }

        return $factory;
    }

    /**
     * Resets the database. This will delete all collections and entries and
     * return true if the database was reset successfully.
     */
    public static function reset(): bool
    {
        return (new Factory())->createApi()->reset();
    }
}
