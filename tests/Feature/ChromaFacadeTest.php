<?php

declare(strict_types=1);

namespace Codewithkyrian\ChromaDB\Tests\Feature;
use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Client;
use Codewithkyrian\ChromaDB\Exceptions\ChromaConnectionException;
use Codewithkyrian\ChromaDB\Factory;
use ReflectionClass;

it('can connect to a normal chroma server', function () {
    $client = ChromaDB::client();

    expect($client)->toBeInstanceOf(Client::class);
});

it('can connect to a chroma server using factory', function () {
    $client = ChromaDB::factory()
        ->withHost('http://localhost')
        ->withPort(8000)
        ->withHeader('X-Chroma-Token', 'test-token')
        ->connect();

    expect($client)->toBeInstanceOf(Client::class);
});

it('throws a connection exception when connecting to a non-existent chroma server', function () {
    ChromaDB::factory()
        ->withHost('http://localhost')
        ->withPort(8002)
        ->connect();
})->throws(ChromaConnectionException::class);

it('can create a cloud factory', function () {
    $factory = ChromaDB::cloud('test-api-key');

    expect($factory)->toBeInstanceOf(Factory::class);

    $reflection = new ReflectionClass($factory);
    $host = $reflection->getProperty('host')->getValue($factory);
    $port = $reflection->getProperty('port')->getValue($factory);
    $headers = $reflection->getProperty('headers')->getValue($factory);

    expect($host)->toBe('https://api.trychroma.com')
        ->and($port)->toBeNull()
        ->and($headers)->toBe(['X-Chroma-Token' => 'test-api-key']);
});

it('can create a local factory', function () {
    $factory = ChromaDB::local('http://custom-host', 1234, 'test-tenant', 'test-db');

    expect($factory)->toBeInstanceOf(Factory::class);

    $reflection = new ReflectionClass($factory);
    $host = $reflection->getProperty('host')->getValue($factory);
    $port = $reflection->getProperty('port')->getValue($factory);
    $tenant = $reflection->getProperty('tenant')->getValue($factory);
    $database = $reflection->getProperty('database')->getValue($factory);

    expect($host)->toBe('http://custom-host')
        ->and($port)->toBe(1234)
        ->and($tenant)->toBe('test-tenant')
        ->and($database)->toBe('test-db');
});
