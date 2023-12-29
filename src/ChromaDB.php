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
     * Creates a new factory instance to configure a custom Alchemy Client
     */
    public static function factory(): Factory
    {
        return new Factory();
    }

    /**
     * Resets the database. This will delete all collections and entries and
     * return true if the database was reset successfully.
     */
    public static function reset() : bool
    {
        return (new Factory())->createApiClient()->reset();
    }
}