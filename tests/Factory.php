<?php

declare(strict_types=1);

use Codewithkyrian\ChromaDB\ChromaDB;

describe('Factory', function () {

    it('can set chroma api key for authentication', function () {
        $factory = ChromaDB::factory()
            ->withHost('https://api.trychroma.com')
            ->withPort(443)
            ->withChromaApiKey('test-api-key-12345');

        $reflection = new ReflectionClass($factory);
        $property = $reflection->getProperty('chromaApiKey');
        $property->setAccessible(true);

        expect($property->getValue($factory))->toBe('test-api-key-12345');
    });

    it('can set both auth token and chroma api key', function () {
        $factory = ChromaDB::factory()
            ->withAuthToken('bearer-token-123')
            ->withChromaApiKey('chroma-key-456');

        $reflection = new ReflectionClass($factory);

        $authTokenProperty = $reflection->getProperty('authToken');
        $authTokenProperty->setAccessible(true);

        $chromaKeyProperty = $reflection->getProperty('chromaApiKey');
        $chromaKeyProperty->setAccessible(true);

        expect($authTokenProperty->getValue($factory))
            ->toBe('bearer-token-123')
            ->and($chromaKeyProperty->getValue($factory))
            ->toBe('chroma-key-456');
    });

    it('includes X-CHROMA-TOKEN header when api key is set', function () {
        $factory = ChromaDB::factory()
            ->withChromaApiKey('test-key-789');

        $apiClient = $factory->createApiClient();

        $reflection = new ReflectionClass($apiClient);
        $clientProperty = $reflection->getProperty('httpClient');
        $clientProperty->setAccessible(true);
        $httpClient = $clientProperty->getValue($apiClient);

        $config = $httpClient->getConfig();

        expect($config['headers']['X-CHROMA-TOKEN'] ?? null)
            ->toBe('test-key-789');
    });

    it('includes both Authorization and X-CHROMA-TOKEN headers when both are set', function () {
        $factory = ChromaDB::factory()
            ->withAuthToken('my-bearer-token')
            ->withChromaApiKey('my-chroma-key');

        $apiClient = $factory->createApiClient();

        $reflection = new ReflectionClass($apiClient);
        $clientProperty = $reflection->getProperty('httpClient');
        $clientProperty->setAccessible(true);
        $httpClient = $clientProperty->getValue($apiClient);

        $config = $httpClient->getConfig();

        expect($config['headers']['Authorization'] ?? null)
            ->toBe('Bearer my-bearer-token')
            ->and($config['headers']['X-CHROMA-TOKEN'] ?? null)
            ->toBe('my-chroma-key');
    });

    it('does not include X-CHROMA-TOKEN header when api key is not set', function () {
        $factory = ChromaDB::factory();

        $apiClient = $factory->createApiClient();

        $reflection = new ReflectionClass($apiClient);
        $clientProperty = $reflection->getProperty('httpClient');
        $clientProperty->setAccessible(true);
        $httpClient = $clientProperty->getValue($apiClient);

        $config = $httpClient->getConfig();

        expect($config['headers']['X-CHROMA-TOKEN'] ?? null)
            ->toBeNull();
    });

});
