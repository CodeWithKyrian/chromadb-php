<?php

declare(strict_types=1);

use Codewithkyrian\ChromaDB\Client;
use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Exceptions\ChromaConnectionException;

it('can connect to a normal chroma server', function () {
    $client = ChromaDB::client();

    expect($client)->toBeInstanceOf(Client::class);
});

it('can connect to a chroma server using factory', function () {
    $client = ChromaDB::factory()
        ->withHost('http://localhost')
        ->withPort(8000)
        ->connect();

    expect($client)->toBeInstanceOf(Client::class);
});

it('throws a connection exception when connecting to a non-existent chroma server', function () {
    ChromaDB::factory()
        ->withHost('http://localhost')
        ->withPort(8002)
        ->connect();
})->throws(ChromaConnectionException::class);
